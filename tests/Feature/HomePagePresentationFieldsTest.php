<?php

namespace Tests\Feature;

use App\Models\HomePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomePagePresentationFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_sections_support_theme_media_cta_and_layout_fields(): void
    {
        $this->assertTrue(Schema::hasColumns('home_pages', [
            'image',
            'image_alt',
            'button_label',
            'button_url',
            'button_style',
            'layout',
            'display_order',
        ]));

        $model = new HomePage();
        foreach (['image', 'image_alt', 'button_label', 'button_url', 'button_style', 'layout', 'display_order'] as $field) {
            $this->assertContains($field, $model->getFillable());
        }
    }
}
