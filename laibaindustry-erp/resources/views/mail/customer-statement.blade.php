<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account statement</title>
</head>
<body style="font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #2B3437; margin: 0; padding: 24px;">
<p style="margin: 0 0 16px 0;">Hello {{ $customer->customer_name }},</p>
<p style="margin: 0 0 16px 0;">Please find your <strong>account statement</strong> attached as a PDF ({{ $pdfFilename }}).</p>
@if(filled($note))
<p style="margin: 0 0 16px 0; padding: 12px; border-left: 3px solid #ABB3B7; background: #F8F9FA;">{!! nl2br(e($note)) !!}</p>
@endif
<p style="margin: 0 0 8px 0;">If you have questions, reply to this email or contact us.</p>
<p style="margin: 24px 0 0 0; font-size: 12px; color: #586064;">© 2026 Laiba Safety. All rights reserved.</p>
</body>
</html>
