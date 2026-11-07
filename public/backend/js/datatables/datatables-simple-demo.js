$(function () {
    var $table = $('#datatablesSimple');
    if (!$table.length || typeof window.simpleDatatables === 'undefined') return;
    if ($table.data('simpleDatatablesInitialized')) return;

    try {
        new window.simpleDatatables.DataTable($table.get(0));
        $table.data('simpleDatatablesInitialized', true);
    } catch (error) {
        window.console.error('Unable to initialize the admin data table.', error);
    }
});
