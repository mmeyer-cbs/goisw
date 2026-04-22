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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Bss_ReorderProduct/js/moment'
], function ($,moment) {

    $.fn.dataTable.moment = function ( format, locale, reverseEmpties ) {
        var types = $.fn.dataTable.ext.type;

        // Add type detection
        types.detect.unshift( function ( d ) {
            if ( d ) {
                // Strip HTML tags and newline characters if possible
                if ( d.replace ) {
                    d = d.replace(/(<.*?>)|(\r?\n|\r)/g, '');
                }

                // Strip out surrounding white space
                d = $.trim( d );
            }

            // Null and empty values are acceptable
            if ( d === '' || d === null ) {
                return 'moment-'+format;
            }

            return moment( d, format, locale, true ).isValid() ?
                'moment-'+format :
                null;
        } );

        // Add sorting method - use an integer for the sorting
        types.order[ 'moment-'+format+'-pre' ] = function ( d ) {
            if ( d ) {
                // Strip HTML tags and newline characters if possible
                if ( d.replace ) {
                    d = d.replace(/(<.*?>)|(\r?\n|\r)/g, '');
                }

                // Strip out surrounding white space
                d = $.trim( d );
            }

            return !moment(d, format, locale, true).isValid() ?
                (reverseEmpties ? -Infinity : Infinity) :
                parseInt( moment( d, format, locale, true ).format( 'x' ), 10 );
        };
    };

});
