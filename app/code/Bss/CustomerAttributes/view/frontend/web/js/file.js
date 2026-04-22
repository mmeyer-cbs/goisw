    /**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(
    [
        'Magento_Ui/js/modal/alert',
        'jquery',
        'jquery/ui',
        'jquery/validate',
        'mage/translate'
    ],
    function (alert, $) {
        return function (config) {
            var maxSize = config.fileSize;
            var data = config;
            try {
                $('#' + data.id).on("change", function () {
                        var error = "";
                        var sel_files = document.getElementById(data.id).files;
                        var len = sel_files.length;
                        for (var i = 0; i < len; i++) {
                            var file = sel_files[i];
                            var fileExtension = data.fileExtension;
                            if (file.size / 1024 > maxSize) {
                                error = $.mage.__('The file size should not exceed ' + maxSize + "KB");
                            }
                            fileExtension = "," + fileExtension + ",";
                            var typeFileName = file.name;
                            typeFileName = getTypeFile(typeFileName, ".");
                            if (fileExtension != "" && data.fileExtension !== "bss_nothing") {
                                if (fileExtension.search(typeFileName) < 0) {
                                    if (error) {
                                        error += "<br>" + $.mage.__('Allowed Input type are ' + data.fileExtension);
                                    } else {
                                        error = $.mage.__('Allowed Input type are ' + data.fileExtension);
                                    }
                                }
                            }
                        }

                        if (error) {
                            alert({
                                title: $.mage.__('Attention'),
                                content: $.mage.__(error),
                                actions: {
                                    always: function () {
                                    }
                                }
                            });
                        }

                        function getTypeFile(typeFile) {
                            var indexSearch = typeFile.lastIndexOf(".");
                            if (indexSearch > 0) {
                                typeFile = typeFile.slice(indexSearch + 1);
                            }
                            return "," + typeFile + ",";
                        }

                    }
                );
            } catch (e) {
                console.log("Sometime error")
            }
            $.validator.addMethod(
                config.validatorSize, function (v, elm) {
                    if (navigator.appName == "Microsoft Internet Explorer") {
                        if (elm.value) {
                            var oas = new ActiveXObject("Scripting.FileSystemObject");
                            var e = oas.getFile(elm.value);
                            var size = e.size;
                        }
                    } else {
                        if (elm.files[0] != undefined) {
                            size = elm.files[0].size;
                        }
                    }
                    if (size != undefined && size / 1024 > maxSize) {
                        return false;
                    }
                    return true;
                }, $.mage.__('The file size should not exceed ' + maxSize + 'KB'));

            if (config.fileExtension != "bss_nothing") {
                var fileExtension = config.fileExtension;
                $.validator.addMethod(
                    config.validatorExtensions, function (v, elm) {

                        var extensions = fileExtension.split(',');
                        if (!v) {
                            return true;
                        }
                        with (elm) {
                            var ext = value.substring(value.lastIndexOf('.') + 1);
                            for (i = 0; i < extensions.length; i++) {
                                if (ext == extensions[i]) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    }, $.mage.__('Allowed input type are ' + fileExtension));
            }
        };
    }
);
