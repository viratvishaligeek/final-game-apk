<?php

namespace App\Console\Commands;

use App\Models\SupportMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateSupportChatMediaToS3 extends Command
{
    protected $signature = 'support-chat:migrate-media-to-s3 {--dry-run : List files without uploading them}';

    protected $description = 'Copy existing private support chat attachments from local storage to the configured S3 disk';

    public function handle(): int
    {
        $local = Storage::disk('local');
        $s3 = Storage::disk('s3');

        $total = 0;
        $copied = 0;
        $failed = 0;

        SupportMessage::withoutGlobalScope('site')
            ->whereNotNull('media_path')
            ->orderBy('id')
            ->chunkById(100, function ($messages) use ($local, $s3, &$total, &$copied, &$failed): void {
                foreach ($messages as $message) {
                    $total++;
                    $path = (string) $message->media_path;

                    if (!$local->exists($path)) {
                        if (!$s3->exists($path)) {
                            $failed++;
                            $this->warn("Missing from both disks: {$path} (message {$message->id})");
                        }
                        continue;
                    }

                    if ($s3->exists($path)) {
                        continue;
                    }

                    if ($this->option('dry-run')) {
                        $this->line("Would copy {$path}");
                        continue;
                    }

                    $stream = $local->readStream($path);
                    if (!is_resource($stream)) {
                        $failed++;
                        $this->error("Unable to read {$path}");
                        continue;
                    }

                    try {
                        $written = $s3->writeStream($path, $stream, ['visibility' => 'private']);
                        if ($written) {
                            $copied++;
                        } else {
                            $failed++;
                            $this->error("Unable to upload {$path}");
                        }
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->error("Upload failed for {$path}: {$exception->getMessage()}");
                    } finally {
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }
                }
            });

        $this->info("Scanned {$total} message attachment(s); copied {$copied}; failed {$failed}.");
        $this->comment('Local files are intentionally retained. Verify object storage before switching SUPPORT_CHAT_DISK to s3.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
