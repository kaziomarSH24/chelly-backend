<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Message</title>
</head>
<body style="margin:0; padding:0; background-color:#f2f5f2; font-family: Arial, Helvetica, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f5f2; padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1f6e2e; padding:28px 30px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:24px; font-weight:bold;">
                                LOVELYS
                            </h1>
                            <p style="margin:6px 0 0; color:#d9ead9; font-size:13px;">
                                New Contact Form Submission
                            </p>
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td style="padding:28px 30px 10px;">
                            <p style="margin:0; color:#333333; font-size:15px; line-height:1.6;">
                                You have received a new message from the contact form. Details are below:
                            </p>
                        </td>
                    </tr>

                    <!-- Details Card -->
                    <tr>
                        <td style="padding:10px 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f9f4; border:1px solid #e0ece0; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0 0 4px; color:#1f6e2e; font-size:12px; font-weight:bold; text-transform:uppercase;">Full Name</p>
                                        <p style="margin:0 0 16px; color:#222222; font-size:15px;">{{ $contactData['full_name'] }}</p>

                                        <p style="margin:0 0 4px; color:#1f6e2e; font-size:12px; font-weight:bold; text-transform:uppercase;">Email Address</p>
                                        <p style="margin:0 0 16px; color:#222222; font-size:15px;">{{ $contactData['email'] }}</p>

                                        <p style="margin:0 0 4px; color:#1f6e2e; font-size:12px; font-weight:bold; text-transform:uppercase;">Subject</p>
                                        <p style="margin:0; color:#222222; font-size:15px;">{{ $contactData['subject'] }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="padding:20px 30px 30px;">
                            <p style="margin:0 0 8px; color:#1f6e2e; font-size:12px; font-weight:bold; text-transform:uppercase;">Message</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #e0ece0; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 20px; color:#333333; font-size:15px; line-height:1.7;">
                                        {{ $contactData['message'] }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#1f6e2e; padding:18px 30px; text-align:center;">
                            <p style="margin:0; color:#d9ead9; font-size:12px;">
                                This email was generated automatically from the Lovelys contact form.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>