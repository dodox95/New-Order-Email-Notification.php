<?php
/**
 * Plugin Name: New Order Email Notification
 * Description: Wysyła powiadomienie e-mail do administratora strony po złożeniu nowego zamówienia.
 * Version: 1.0
 * Author: TraviLabs
 * License: GPL-2.0+
 */

// Prevent direct file access
defined('ABSPATH') || exit;

// Send email notification to admin when a new order is created
function send_new_order_email_notification($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    $email = get_option('admin_email');
    $subject = 'Nowe zamówienie: #' . $order_id;

    // Build the message
    $message = '<html><body>';
    $message .= '<style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>';

    $message .= '<p>Witaj! Otrzymałeś nowe zamówienie o numerze: <strong>#' . $order_id . '</strong>.</p>';

    $message .= '<h3>Dane klienta:</h3>';
    $message .= '<p>Imię i nazwisko: ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '<br>';
    $message .= 'E-mail: ' . $order->get_billing_email() . '<br>';
    $message .= 'Telefon: ' . $order->get_billing_phone() . '</p>';

    $message .= '<h3>Dane do wysyłki:</h3>';
    $message .= '<p>' . $order->get_formatted_shipping_address() . '</p>';

    $message .= '<h3>Produkty zamówione:</h3>';
    $message .= '<table><thead><tr><th>Produkt</th><th>Ilość</th></tr></thead><tbody>';
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $message .= '<tr><td>' . $product->get_name() . '</td><td>' . $item->get_quantity() . '</td></tr>';
    }
    $message .= '</tbody></table>';

    $message .= '<h3>Podsumowanie zamówienia:</h3>';
    $message .= '<p>Forma płatności: ' . $order->get_payment_method_title() . '<br>';
    $message .= 'Dostawa: ' . $order->get_shipping_method() . '<br>';
    $message .= 'Koszt dostawy: ' . $order->get_shipping_total() . ' ' . $order->get_currency() . '<br>';
    $message .= 'Całkowity koszt zamówienia: ' . $order->get_total() . ' ' . $order->get_currency() . '</p>';

    $message .= '</body></html>';

// Set content type for HTML emails
function set_html_content_type() {
    return 'text/html';
}
add_filter('wp_mail_content_type', 'set_html_content_type');

// Send email
wp_mail($email, $subject, $message);

// Reset content type to avoid conflicts
remove_filter('wp_mail_content_type', 'set_html_content_type');


}

add_action('woocommerce_new_order', 'send_new_order_email_notification');

