<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, sans-serif;">

    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <!-- Header / Logo -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <img src="https://smarttradeind.com/assets/images/Smart%20Trade%20Logo%20Redesign%202-2.png"
                                alt="Smart Trade" width="250" style="display:block;">
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333;">
                            <h2 style="margin-top:0;">Password Reset Request</h2>

                            <p>Dear {{ $name }},</p>

                            <p>We received a request to reset your password. Use the OTP below to proceed:</p>

                            <!-- OTP Box -->
                            <div style="text-align:center; margin:10px 0;">
                                <span
                                    style="display:inline-block; padding:15px 25px; font-size:24px; letter-spacing:4px; font-weight:bold; color:#0d6efd; border:2px dashed #0d6efd; border-radius:6px;">
                                    {{ $otp }}
                                </span>
                            </div>
                            <p style="color:#d9534f;"><strong>Do not share this OTP with anyone.</strong></p>

                            <p>If you did not request this, please ignore this email or contact support.</p>

                            <p>Thanks,<br><strong>Smart Trade Team</strong></p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#f1f1f1; padding:15px; font-size:12px; color:#777;">
                            © 2026 SmartTrade. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>