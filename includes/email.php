<?php
/**
 * Email Functions with SMTP Support
 * Native PHP implementation without external libraries
 */

/**
 * Send email using PHP mail() or SMTP
 */
function sendEmailAdvanced($to, $subject, $body, $isHTML = true) {
    global $db;
    
    // Get SMTP settings
    $smtp_enabled = getSetting('smtp_enabled', '0') === '1';
    
    if ($smtp_enabled) {
        return sendEmailSMTP($to, $subject, $body, $isHTML);
    } else {
        return sendEmailNative($to, $subject, $body, $isHTML);
    }
}

/**
 * Send email using native PHP mail()
 */
function sendEmailNative($to, $subject, $body, $isHTML = true) {
    $from_email = getSetting('smtp_from_email', 'noreply@3dstore.com');
    $from_name = getSetting('smtp_from_name', '3D Store');
    
    $headers = [];
    $headers[] = "From: {$from_name} <{$from_email}>";
    $headers[] = "Reply-To: {$from_email}";
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    if ($isHTML) {
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
    }
    
    $result = mail($to, $subject, $body, implode("\r\n", $headers));
    
    // Log email
    logEmail($to, $subject, $body, $result ? 'sent' : 'failed');
    
    return $result;
}

/**
 * Send email using SMTP (socket connection)
 */
function sendEmailSMTP($to, $subject, $body, $isHTML = true) {
    $smtp_host = getSetting('smtp_host', '');
    $smtp_port = (int)getSetting('smtp_port', '587');
    $smtp_username = getSetting('smtp_username', '');
    $smtp_password = getSetting('smtp_password', '');
    $smtp_encryption = getSetting('smtp_encryption', 'tls');
    $from_email = getSetting('smtp_from_email', '');
    $from_name = getSetting('smtp_from_name', '3D Store');
    
    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
        logEmail($to, $subject, $body, 'failed', 'SMTP not configured');
        return false;
    }
    
    try {
        // Create socket connection
        $socket = fsockopen(
            ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host,
            $smtp_port,
            $errno,
            $errstr,
            30
        );
        
        if (!$socket) {
            throw new Exception("Connection failed: {$errstr} ({$errno})");
        }
        
        // Read server greeting
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            throw new Exception("Server greeting failed: {$response}");
        }
        
        // Send EHLO
        fputs($socket, "EHLO {$smtp_host}\r\n");
        $response = fgets($socket, 515);
        
        // Start TLS if needed
        if ($smtp_encryption === 'tls') {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                throw new Exception("STARTTLS failed: {$response}");
            }
            
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            fputs($socket, "EHLO {$smtp_host}\r\n");
            $response = fgets($socket, 515);
        }
        
        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        fgets($socket, 515);
        
        fputs($socket, base64_encode($smtp_username) . "\r\n");
        fgets($socket, 515);
        
        fputs($socket, base64_encode($smtp_password) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '235') {
            throw new Exception("Authentication failed: {$response}");
        }
        
        // MAIL FROM
        fputs($socket, "MAIL FROM: <{$from_email}>\r\n");
        fgets($socket, 515);
        
        // RCPT TO
        fputs($socket, "RCPT TO: <{$to}>\r\n");
        fgets($socket, 515);
        
        // DATA
        fputs($socket, "DATA\r\n");
        fgets($socket, 515);
        
        // Email headers and body
        $email_data = "From: {$from_name} <{$from_email}>\r\n";
        $email_data .= "To: {$to}\r\n";
        $email_data .= "Subject: {$subject}\r\n";
        $email_data .= "MIME-Version: 1.0\r\n";
        if ($isHTML) {
            $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $email_data .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        $email_data .= "\r\n";
        $email_data .= $body;
        $email_data .= "\r\n.\r\n";
        
        fputs($socket, $email_data);
        $response = fgets($socket, 515);
        
        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        $success = substr($response, 0, 3) == '250';
        logEmail($to, $subject, $body, $success ? 'sent' : 'failed');
        
        return $success;
    } catch (Exception $e) {
        logEmail($to, $subject, $body, 'failed', $e->getMessage());
        return false;
    }
}

/**
 * Log email to database
 */
function logEmail($to, $subject, $body, $status, $error = null) {
    global $db;
    try {
        $db->insert(
            "INSERT INTO email_logs (recipient, subject, body, status, error_message, sent_at, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
            [$to, $subject, $body, $status, $error]
        );
    } catch (Exception $e) {
        // Silently fail logging
    }
}

/**
 * Generate HTML invoice email
 */
function generateInvoiceEmailHTML($order, $items) {
    $store_name = getSetting('site_name_ar', '3D Store');
    $store_phone = getSetting('store_phone', '');
    $store_address = getSetting('store_address', '');
    
    $html = '
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px 20px; }
        .info-row { display: flex; justify-content: space-between; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 6px; }
        .info-box h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
        .info-box p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #f5f5f5; padding: 12px; text-align: right; border-bottom: 2px solid #ddd; }
        table td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-row { background: #f9f9f9; font-weight: bold; }
        .footer { background: #f5f5f5; padding: 20px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . htmlspecialchars($store_name) . '</h1>
            <p>فاتورة رقم: ' . htmlspecialchars($order['order_number']) . '</p>
        </div>
        
        <div class="content">
            <div class="info-row">
                <div class="info-box">
                    <h3>معلومات العميل</h3>
                    <p><strong>' . htmlspecialchars($order['customer_name']) . '</strong></p>
                    <p>' . htmlspecialchars($order['customer_phone']) . '</p>
                    <p>' . htmlspecialchars($order['customer_email'] ?? '') . '</p>
                </div>
                <div class="info-box" style="text-align: left;">
                    <h3>معلومات الفاتورة</h3>
                    <p><strong>التاريخ:</strong> ' . date('Y-m-d', strtotime($order['created_at'])) . '</p>
                    <p><strong>الحالة:</strong> ' . htmlspecialchars($order['status']) . '</p>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($items as $item) {
        $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['product_name']) . '</td>
                        <td>' . number_format($item['quantity']) . '</td>
                        <td>' . number_format($item['unit_price'], 2) . ' ' . htmlspecialchars($order['currency']) . '</td>
                        <td>' . number_format($item['total'], 2) . ' ' . htmlspecialchars($order['currency']) . '</td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: left;"><strong>المجموع الفرعي:</strong></td>
                        <td><strong>' . number_format($order['subtotal'], 2) . ' ' . htmlspecialchars($order['currency']) . '</strong></td>
                    </tr>';
    
    if ($order['tax'] > 0) {
        $html .= '
                    <tr>
                        <td colspan="3" style="text-align: left;">الضريبة:</td>
                        <td>' . number_format($order['tax'], 2) . ' ' . htmlspecialchars($order['currency']) . '</td>
                    </tr>';
    }
    
    if ($order['shipping_cost'] > 0) {
        $html .= '
                    <tr>
                        <td colspan="3" style="text-align: left;">الشحن:</td>
                        <td>' . number_format($order['shipping_cost'], 2) . ' ' . htmlspecialchars($order['currency']) . '</td>
                    </tr>';
    }
    
    if ($order['discount'] > 0) {
        $html .= '
                    <tr>
                        <td colspan="3" style="text-align: left;">الخصم:</td>
                        <td style="color: #e74c3c;">- ' . number_format($order['discount'], 2) . ' ' . htmlspecialchars($order['currency']) . '</td>
                    </tr>';
    }
    
    $html .= '
                    <tr class="total-row">
                        <td colspan="3" style="text-align: left; font-size: 16px;">المجموع الكلي:</td>
                        <td style="font-size: 16px; color: #27ae60;">' . number_format($order['total_amount'], 2) . ' ' . htmlspecialchars($order['currency']) . '</td>
                    </tr>
                </tfoot>
            </table>
            
            <p style="color: #666; font-size: 14px; margin-top: 30px;">
                شكراً لتعاملكم معنا. إذا كان لديكم أي استفسار، يرجى التواصل معنا.
            </p>
        </div>
        
        <div class="footer">
            <p>' . htmlspecialchars($store_name) . '</p>
            <p>' . htmlspecialchars($store_address) . '</p>
            <p>الهاتف: ' . htmlspecialchars($store_phone) . '</p>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}
?>