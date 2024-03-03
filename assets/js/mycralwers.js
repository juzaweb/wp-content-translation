jQuery(document).ready(function($) {
    function post_translate(ids, index, languages, languageIndex) {
        const checkBox = $('#cb-select-'+ids[index]);
        if (checkBox.is(':visible')) {
            checkBox.hide().after(
                `<img src="/wp-includes/images/spinner.gif" alt="" style="margin: 0 0 0 8px;">`
            );
        }

        jQuery.post(
            ajaxurl,
            {
                'action': 'post_translate',
                'post_id': ids[index],
                'to_locale': languages[languageIndex],
            },
            function (response) {
                console.log('The server responded: ', response);
                if (response.errors) {
                    alert(response.errors[0].message);
                    return false;
                }

                if (languageIndex + 1 < languages.length) {
                    post_translate(ids, index, languages, languageIndex + 1);
                    return false;
                }

                if (index + 1 < ids.length) {
                    post_translate(ids, index + 1, languages, 0);
                    return false;
                }

                location.reload();
            }
        );
    }

    $("#bulk-action-selector-top, #bulk-action-selector-bottom").on('change', function (e) {
        var $this = $(this);

        if ($this.val() == 'translate') {

            $this.after('<select id="languages-select" name="languages[]" multiple>' +
                //'<option value="en">English</option>' +
                //'<option value="es">Spanish</option>' +
                '</select>');

            $('#languages-select').select2({
                width: '200px',
                delay: 250,
                cache: true,
                ajax: {
                    url: 'https://mycrawlers.com/api/crawl/languages',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                        };
                    },
                    processResults: function (res) {

                        return {
                            results: res.data.map(function (item) {
                                return {
                                    id: item.code,
                                    text: item.name
                                };
                            })
                        };
                    }
                }
            });
        } else {
            $("#languages-select").select2('destroy').remove();
        }
    });

    $(document).on('click', '#doaction', function() {
        let action = $(this).closest('#posts-filter').find('select[name="action"]').val();

        if (action != 'translate') {
            return;
        }

        const languages = $('#languages-select').val();
        if (languages.length <= 0) {
            return false;
        }

        const values = $('input[name="post[]"]:checked').map(function () {
            return $(this).val();
        }).get();

        if (values.length <= 0) {
            return false;
        }

        post_translate(values, 0, languages, 0);

        return false;
    });
});
