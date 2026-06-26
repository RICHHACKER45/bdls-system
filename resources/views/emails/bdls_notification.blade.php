<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 20px 10px">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1)">
                    <!-- 1. HEADER SECTION -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 30px 20px; text-align: center; border-bottom: 4px solid #dc2626">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase">Barangay Doña Lucia</h1>
                            <p style="color: #94a3b8; margin: 5px 0 0 0; font-size: 14px; font-weight: 600">Official Service Portal</p>
                        </td>
                    </tr>

                    <!-- 2. BODY SECTION -->
                    <tr>
                        <td style="padding: 40px 30px; color: #334155">
                            @php
                                // SMART LOGIC: Hanapin kung mayroong eksaktong 6-digit number (OTP) sa loob ng message
                                $isOtp = preg_match('/(\d{6})/', $mailMessage, $matches);
                                $otpCode = $isOtp ? $matches[0] : null;
                                
                                // Linisin ang text para mahiwalay ang message sa mismong OTP code UI
                                $displayMessage = $isOtp ? str_replace($otpCode, '', $mailMessage) : $mailMessage;
                            @endphp

                            <!-- Main Message Paragraph -->
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; text-align: justify; white-space: pre-line">{{ trim($displayMessage) }}</p>

                            <!-- DYNAMIC OTP HIGHLIGHT BOX (Lalabas lang kapag may OTP) -->
                            @if ($isOtp)
                                <div style="text-align: center; margin: 35px 0">
                                    <div style="display: inline-block; background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px 40px">
                                        <span style="display: block; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 10px; letter-spacing: 2px"> Iyong Verification Code </span>
                                        <span style="font-size: 46px; font-weight: 900; color: #0f172a; letter-spacing: 10px"> {{ $otpCode }} </span>
                                    </div>
                                    <p style="margin: 15px 0 0 0; font-size: 13px; color: #ef4444; font-weight: bold">⚠️ Huwag ibahagi ang code na ito sa iba.</p>
                                </div>
                            @endif

                            <!-- CALL TO ACTION (Lalabas kapag Announcements o Status Update) -->
                            @if (!$isOtp)
                                <div style="margin-top: 35px; border-top: 1px solid #e2e8f0; padding-top: 25px; text-align: center">
                                    <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #dc2626; color: #ffffff; text-decoration: none; font-weight: bold; padding: 12px 24px; border-radius: 8px; font-size: 14px"> Pumunta sa BDLS Portal </a>
                                </div>
                            @endif
                        </td>
                    </tr>

                    <!-- 3. FOOTER SECTION -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 25px 20px; text-align: center; border-top: 1px solid #e2e8f0">
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #64748b; font-weight: bold">Ito ay isang automated message mula sa BDLS System.<br />Huwag i-reply ang email na ito.</p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8">&copy; {{ date('Y') }} Barangay Doña Lucia, Quezon, Nueva Ecija. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
