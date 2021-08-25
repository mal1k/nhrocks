<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/design.php
	# ----------------------------------------------------------------------------------------------------

?>
	<script type="text/javascript" src="<?=DEFAULT_URL?>/scripts/editarea/edit_area/edit_area_full.js"></script>

    <script>

        //Theme changing
        function JS_submit(value) {
            $("#select_theme").attr("value", value);
            $('#loading_theme').removeClass('hidden');
            $("#theme").submit();
        }

        //Colors changing
        function JS_submitColors(type) {
			if (type == "reset") {
                $("#action").attr("value", "reset");
                bootbox.confirm('<?=system_showText(LANG_SITEMGR_COLORS_RESET_CONFIRM);?>', function(result) {
                    if (result) {
                        document.color_palette.submit();
                    } else {
                        btn = $('.action-save');
                        btn.button('reset');
                    }
                });
			} else {
                document.color_palette.submit();
			}
		}

        function InitEDitor() {
            editAreaLoader.init({
                id : "textarea",
                syntax: "<?=$editorSyntax?>",
                start_highlight: true,
                language: "<?=$editorLang?>",
                allow_toggle: false
            });
        }

        $(document).ready(function() {
            if ($('#textarea').length) {
                InitEDitor();
            }
            if ($('#image_border_range').length) {
                $(document).on('input change', '#image_border_range', function() {
                    $('#image_border_sample').css('border-radius', $(this).val() + 'px');
                    $("#image_border").val($(this).val());
                    $('#image_border_size').html('(' + $(this).val() + 'px)');
                });
            }
            if ($('#input_border_range').length) {
                $(document).on('input change', '#input_border_range', function() {
                    $('#input_border_sample').css('border-radius', $(this).val() + 'px');
                    $("#input_border").val($(this).val());
                    $('#input_border_size').html('(' + $(this).val() + 'px)');
                });
            }
            if ($('#font_range').length) {
                $(document).on('input change', '#font_range', function() {
                    $('#font_sample').css('font-size', $(this).val() + 'px');
                    $("#font").val($(this).val());
                    $('#font_size').html('(' + $(this).val() + 'px)');
                });
            }
            if ($('.font-family').length) {
                $.ajax({
                    url: '/<?=SITEMGR_ALIAS?>/design/colors-fonts/fonts.json',
                    method: 'GET',
                    async: false,
                    success: function (fonts) {
                        var options = [];

                        fonts.items.forEach(function (item) {
                            var value;
                            var variantString = ':';
                            item.variants.forEach(function (variant) {
                                if(/^\d+$/.test(variant)) {
                                    if (variantString !== ':') {
                                        variantString += ',';
                                    }
                                    variantString += variant;
                                }
                            });
                            if(variantString !== ':') {
                                value = item.family + variantString;
                            } else {
                                value = item.family;
                            }
                            options.push({family:item.family, value:value});
                        });

                        $('.font-select').selectize({
                            sortField: null,
                            persist: false,
                            maxItems: 1,
                            openOnFocus: false,
                            valueField: 'value',
                            labelField: 'family',
                            searchField: ['family'],
                            options: options,
                            render: {
                                option: function (item, escape) {
                                    var label = item.family;
                                    return '<div>' +
                                        '<span class="label-name">' + escape(label) + '</strong>' +
                                        '</div>';
                                }
                            },
                            onInitialize: function(){
                                var family = this.$input.data('value');
                                this.setValue(family, true);
                            },
                            onChange: function (value) {
                                var link = document.createElement('link');
                                link.setAttribute('rel', 'stylesheet');
                                link.setAttribute('type', 'text/css');
                                link.setAttribute('href', 'https://fonts.googleapis.com/css?family='+value+':regular');
                                document.head.appendChild(link);
                                $('#'+this.$input.data('type')+'_sample').css('font-family', value);
                            },
                        });
                    }
                });
            }

        });

    </script>
