jQuery(document).ready(function($) {
    // Watch the bulk actions dropdown, looking for custom bulk actions
    $("#bulk-action-selector-top, #bulk-action-selector-bottom").on('change', function (e) {
        var $this = $(this);

        if ($this.val() == 'translate') {
            $this.after('<select id="languages-select" name="languages[]" multiple></select>');

            $('#languages-select').select2({
                width: '200px',
                ajax: {
                    url: 'https://mycrawlers.com/api/crawl/languages',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: params.term,
                            search: $.trim(params.term),
                            page: params.page,
                        };
                    },
                }
            });
        } else {
            $("#languages-select").select2('destroy').remove();
        }
    });
});
