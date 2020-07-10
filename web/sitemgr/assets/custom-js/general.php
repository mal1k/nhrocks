<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/general.php
	# ----------------------------------------------------------------------------------------------------

    if (system_getCountAccountsItems() <= MAXIMUM_NUMBER_OF_ITEMS_IN_SELECTIZE) {
        system_generateAccountDropdown($auxAccountSelectize);
    }
?>
	<script>

        function activationByPhone() {
            if (document.getElementById("activation_by_phone").checked) {
                document.getElementById("table_activation").className = "form-group show";
            } else {
                document.getElementById("table_activation").className = "form-group hidden";
            }
        }

        function toogleTrans(obj) {
            if (obj.checked == true) {
                document.getElementById("trans_form").style.display = 'block';
            } else {
                document.getElementById("trans_form").style.display = 'none';
            }
        }

        function emptyDate(obj) {
            if (obj.value == "00/00/0000") {
                return true;
            } else {
                return false;
            }
        }

        function submitFormSettings() {
            btn = $("#btn-save");
            $.post("<?=$modalSettingsPath?>", $("#setting_item").serialize(), function(result) {
                if ($.trim(result) != "error") {
                    if ($.trim(result) == "1") {
                        window.location.href = '<?=$url_redirect?>/index.php?message=1';
                    } else {
                        btn.button('reset');
                        $("#warningSettings").removeClass('hidden');
                        $("#warningSettings").html(result);
                    }
                }
            });
        }

        $('#modal-settings').on('show.bs.modal', function (e) {
            if ($('#warningSettings').length <= 0) {
                $("#settings-content").html('');
                $.get('<?=$modalSettingsPath?>', {
                    domain_id: <?=SELECTED_DOMAIN_ID?>,
                    id: $("#setting-id").val()
                }, function (ret) {
                    $("#settings-content").html(ret);
                    initPlugins();

                    <?php if (!empty($auxAccountSelectize) and is_array($auxAccountSelectize) && count($auxAccountSelectize)) { ?>
                    $('.mail-select').selectize({
                        sortField: null,
                        persist: false,
                        maxItems: 1,
                        openOnFocus: false,
                        valueField: 'id',
                        labelField: 'name',
                        searchField: ['name', 'email'],
                        options: [
                            <? foreach ($auxAccountSelectize as $accSelectize) { ?>
                                {
                                    email: '<?=addslashes($accSelectize["email"])?>',
                                    name: '<?=addslashes($accSelectize["name"])?>',
                                    id: <?=db_formatNumber($accSelectize["id"])?>},
                            <? } ?>
                        ],
                        render: {
                            item: function(item, escape) {
                                return '<div class="selectize-dropdown-content">' +
                                    (item.name ? '<span class="name">' + escape(item.name) + ' </span> ' : ' <span class="email">' + escape(item.email) + ' </span> ')
                                '</div>';
                            },
                            option: function(item, escape) {
                                var label = item.name || item.email;
                                var caption = item.name ? item.email : null;
                                return '<div>' +
                                    '<span class="label-name">' + escape(label) + '</strong>' +
                                    (caption ? '<i>' + escape(caption) + '</i>' : '') +
                                '</div>';
                            }
                        },
                        onInitialize: function(){
                            var $this = this;
                            var account_id = $this.$input.data('value');
                            $this.setValue(account_id, true);
                        },
                    });
                    <?php } ?>
                });
            }
        })

        $('#modal-settings').on('hidden.bs.modal', function (e) {
            $("#settings-content").html('');
        })

    </script>
