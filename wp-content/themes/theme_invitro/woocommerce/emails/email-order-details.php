<?php

/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined('ABSPATH') || exit;

$text_align = is_rtl() ? 'right' : 'left';

do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email); ?>

<h2>
    <?php
    if ($sent_to_admin) {
        $before = '<a class="link" href="' . esc_url($order->get_edit_order_url()) . '">';
        $after  = '</a>';
    } else {
        $before = '';
        $after  = '';
    }
    /* translators: %s: Order ID. */
    echo wp_kses_post($before . sprintf(__('[Order #%s]', 'woocommerce') . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format('c'), wc_format_datetime($order->get_date_created())));
    ?>
</h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Price', 'woocommerce'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($order->get_items() as $item_id => $item) {
                $product_id = apply_filters('woocommerce_order_item_product_id', $item->get_product_id(), $item, $order);
                $product = wc_get_product($product_id);

                if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
                    continue;
                }

                $is_visible = $product && $product->is_visible();
                $product_permalink = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);

                echo '<tr class="' . esc_attr(apply_filters('woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order)) . '">';
                echo '<td class="woocommerce-table__product-name product-name">';
                echo wp_kses_post(apply_filters('woocommerce_order_item_name', $product_permalink ? sprintf('<a href="%s">%s</a>', $product_permalink, $item->get_name()) : $item->get_name(), $item, $is_visible));

                $qty = $item->get_quantity();
                $refunded_qty = $order->get_qty_refunded_for_item($item_id);
                $qty_display = $refunded_qty ? '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - ($refunded_qty * -1)) . '</ins>' : esc_html($qty);

                //echo apply_filters('woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $qty_display) . '</strong>', $item);

                do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
                wc_display_item_meta($item);
                do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);

                if (function_exists('obtener_promociones_para_pedido')) {
                    $promociones_validas = obtener_promociones_para_pedido($product_id, $qty, $order);
                    if (!empty($promociones_validas)) {
                        echo '<ul class="product-promotions-email">';
                        foreach ($promociones_validas as $promocion) {
                            echo '<li>' . esc_html($promocion) . '</li>';
                        }
                        echo '</ul>';
                    }
                }

                if (have_rows('listado_de_etiquetas', 'option')) {
                    while (have_rows('listado_de_etiquetas', 'option')) {
                        the_row();

                        $nombre_etiqueta = get_sub_field('nombre_etiqueta', 'option');
                        $productos = get_sub_field('productos', 'option');

                        if ($productos) {
                            if (is_array($productos) && isset($productos[0]->ID)) {
                                if (in_array($product_id, wp_list_pluck($productos, 'ID'))) {
                                    echo '<div class="product-tag">' . '<p>' . 'Descuento: ' . esc_html($nombre_etiqueta) . '</p>' . '</div>';
                                }
                            } elseif (in_array($product_id, $productos)) {
                                echo '<div class="product-tag">' . '<p>' . 'Descuento: ' . esc_html($nombre_etiqueta) . '</p>' . '</div>';
                            }
                        }
                    }
                }

                echo '</td>';

                echo '<td class="woocommerce-table__product-quantity product-quantity">' . esc_html($qty_display) . '</td>';
                echo '<td class="woocommerce-table__product-total product-total">' . $order->get_formatted_line_subtotal($item) . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <?php
            $item_totals = $order->get_order_item_totals();
            if ($item_totals) {
                $i = 0;
                foreach ($item_totals as $total) {
                    $i++;
            ?>
                    <tr>
                        <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr($text_align); ?>; <?php echo (1 === $i) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['label']); ?></th>
                        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; <?php echo (1 === $i) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['value']); ?></td>
                    </tr>
                <?php
                }
            }
            if ($order->get_customer_note()) {
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Note:', 'woocommerce'); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php echo wp_kses_post(nl2br(wptexturize($order->get_customer_note()))); ?></td>
                </tr>
            <?php
            }
            ?>
        </tfoot>
    </table>
</div>
<style>
    ul.x_product-promotions-email {
        padding-left: 0;
    }

    .x_product-promotions-email li {
        list-style: none;
    }
</style>

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email); ?>