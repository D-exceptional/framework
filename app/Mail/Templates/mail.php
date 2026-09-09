<?php

declare(strict_types=1);

$hostUrl = config('app.base_path', '/projects/showcase/jobspot'); 

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv='Content-type' content='text/html; charset=utf-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1' />
    <meta http-equiv='X-UA-Compatible' content='IE=edge' />
    <meta name='format-detection' content='date=no' />
    <meta name='format-detection' content='address=no' />
    <meta name='format-detection' content='telephone=no' />
    <meta name='x-apple-disable-message-reformatting' />
    <link href='https://fonts.googleapis.com/css?family=Yantramanav:300,400,500,700' rel='stylesheet' />
    <title>Email Template</title>
    <style type='text/css' media='screen'>
        /* Linked Styles */
        body { padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#f4f4f4; -webkit-text-size-adjust:none }
        a { color:#2f774a; text-decoration:none }
        p { padding:0 !important; margin:0 !important } 
        img { -ms-interpolation-mode: bicubic; /* Allow smoother rendering of resized image in Internet Explorer */ }
        .mcnPreviewText { display: none !important; }

                
        /* Mobile styles */
        @media only screen and (max-device-width: 480px), only screen and (max-width: 480px) {
            u + .body .gwfw { width:100% !important; width:100vw !important; }

            .m-shell { width: 100% !important; min-width: 100% !important; }
            
            .m-center { text-align: center !important; }
            
            .center { margin: 0 auto !important; }
            .nav { text-align: center !important; }
            .text-top { line-height: 22px !important; }
            
            .td { width: 100% !important; min-width: 100% !important; }
            .bg { height: auto !important; -webkit-background-size: cover !important; background-size: cover !important; }

            .m-br-15 { height: 15px !important; }
            .p30-15 { padding: 30px 15px !important; }
            .p0-15-30 { padding: 0px 15px 30px 15px !important; }
            .pb40 { padding-bottom: 40px !important; }
            .pb0 { padding-bottom: 0px !important; }
            .pb20 { padding-bottom: 20px !important; }

            .m-td,
            .m-hide { display: none !important; width: 0 !important; height: 0 !important; font-size: 0 !important; line-height: 0 !important; min-height: 0 !important; }

            .m-height { height: auto !important; }

            .m-block { display: block !important; }

            .fluid-img img { width: 100% !important; max-width: 100% !important; height: auto !important; }

            .column,
            .column-top,
            .column-dir,
            .column-bottom,
            .column-dir-top,
            .column-dir-bottom { float: left !important; width: 100% !important; display: block !important; }

            .content-spacing { width: 15px !important; }
        }
    </style>
</head>
<body class='body' style='padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#f4f4f4; -webkit-text-size-adjust:none;'>
    <table width='100%' border='0' cellspacing='0' cellpadding='0' bgcolor='#f4f4f4' class='gwfw'>
        <tr>
            <td align='center' valign='top'>
                <!-- Main -->
                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td align='center' style='padding-bottom: 40px;' class='pb0'>
                            <!-- Shell -->
                            <table width='650' border='0' cellspacing='0' cellpadding='0' class='m-shell'>
                                <tr>
                                    <td class='td' style='width:650px; min-width:650px; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;'>
                                    
                                        <!-- Header -->
                                        <table width='100%' border='0' cellspacing='0' cellpadding='0' bgcolor='#ffffff' style='border-radius: 6px 6px 0px 0px;'>
                                            <tr>
                                                <td style='padding: 40px;'>
                                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                        <tr>
                                                            <th class='column' width='118' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;'>
                                                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                    <tr>
                                                                        <!--<td class='img m-center' style='font-size:0pt; line-height:0pt; text-align:left;'><a href='#'><img src='<?= $hostUrl ?>/assets/img/logo.jpg' width='80' height='50' border='0' alt='' /></a></td>-->
                                                                    </tr>
                                                                </table>
                                                            </th>
                                                            <th style='padding-bottom:20px !important; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;' class='column' width='1'></th>
                                                            <th class='column' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;'>
                                                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                    <tr>
                                                                        <td align='right'>
                                                                            <table border='0' cellspacing='0' cellpadding='0' class='center' style='text-align:center;'>
                                                                                <tr>
                                                                                    <td class='img' width='20' style='font-size:0pt; line-height:0pt; text-align:left;'><img src='<?= $hostUrl ?>/assets/img/bullet.jpg' width='10' height='3' border='0' alt='' /></td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </th>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- END Header -->
                                        
                                        <!-- Article Image On The Left -->
                                        <div mc:repeatable='Select' mc:variant='Article Image On The Left'>
                                            <table width='100%' border='0' cellspacing='0' cellpadding='0' bgcolor='#ffffff'>
                                                <tr>
                                                    <td style='padding: 0px 40px 40px 40px;' class='p0-15-30'>
                                                        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                            <tr>
                                                                <th class='column' width='260' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;'>
                                                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                        <tr>
                                                                            <td class='fluid-img' style='font-size:0pt; line-height:0pt; text-align:left;'><a href='#'><img src='<?= $hostUrl ?>/assets/img/logo.jpg' style='border-radius: 10px;width: 260px;height: 200px !important;' border='0' alt='' /></a></td>
                                                                        </tr>
                                                                    </table>
                                                                </th>
                                                                <th style='padding-bottom:20px !important; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;' class='column' width='60'></th>
                                                                <th class='column' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;'>
                                                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                        <tr>
                                                                            <td class='text' style='padding-bottom: 25px; color:#666666; font-family:Arial, sans-serif; font-size:16px; line-height:30px; text-align:left; min-width:auto !important;'>{{message}}</td>
                                                                        </tr>
                                                                    </table>
                                                                </th>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <!-- END Article Image On The Left -->

                                        <!-- Footer -->
                                        <table width='100%' border='0' cellspacing='0' cellpadding='0' bgcolor='#ffffff'>
                                            <tr>
                                                <td style='padding: 80px 40px; border-top: 3px solid #f4f4f4;' class='p30-15'>
                                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                        <tr>
                                                            <td style='padding-bottom: 40px;'>
                                                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                    <tr>
                                                                        <th class='column-top' width='110' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal; vertical-align:top;'>
                                                                            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                                <tr>
                                                                                    <td class='img m-center' style='font-size:0pt; line-height:0pt; text-align:left;'><a href='#'><img src='<?= $hostUrl ?>/assets/img/logo.jpg' width='50' height='50' border='0' alt='' /></a></td>
                                                                                </tr>
                                                                            </table>
                                                                        </th>
                                                                        <th style='padding-bottom:25px !important; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;' class='column' width='20'></th>
                                                                        <th class='column' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;'>
                                                                            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                                <tr>
                                                                                    <td align='right'>
                                                                                        <table class='center' border='0' cellspacing='0' cellpadding='0' style='text-align:center;'>
                                                                                            <tr>
                                                                                                <td class='img' width='55' style='font-size:0pt; line-height:0pt; text-align:left;'><a href='#' target='_blank'><img src='<?= $hostUrl ?>/assets/img/ico_facebook.jpg' width='34' height='34' border='0' alt='' /></a></td>
                                                                                                <td class='img' width='55' style='font-size:0pt; line-height:0pt; text-align:left;'><a href='#' target='_blank'><img src='<?= $hostUrl ?>/assets/img/ico_twitter.jpg' width='34' height='34' border='0' alt='' /></a></td>
                                                                                                <td class='img' width='55' style='font-size:0pt; line-height:0pt; text-align:left;'><a href='#' target='_blank'><img src='<?= $hostUrl ?>/assets/img/ico_instagram.jpg' width='34' height='34' border='0' alt='' /></a></td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </th>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                        <tr>
                                                            <th class='column' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;'>
                                                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                    <tr>
                                                                        <td class='text-footer m-center' style='padding-bottom: 15px; color:#999999; font-family:Arial, sans-serif; font-size:14px; line-height:18px; text-align:left; min-width:auto !important;'>JobSpot</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class='text-footer2 m-center' style='color:#999999; font-family:Arial, sans-serif; font-size:12px; line-height:16px; text-align:left; min-width:auto !important;'>Lagos, Nigeria</td>
                                                                    </tr>
                                                                </table>
                                                            </th>
                                                            <th style='padding-bottom:25px !important; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;' class='column' width='20'></th>
                                                            <th class='column-bottom' width='118' style='font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal; vertical-align:bottom;'>
                                                                <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                                    <tr>
                                                                        <td class='text-footer right m-center' style='color:#999999; font-family:Arial, sans-serif; font-size:14px; line-height:18px; min-width:auto !important; text-align:right;'><a href='#' class='link-grey-u' style='color:#999999; text-decoration:underline;'><span class='link-grey-u' style='color:#999999; text-decoration:underline;'>Unsubscribe</span></a></td>
                                                                    </tr>
                                                                </table>
                                                            </th>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- END Footer -->
                                    </td>
                                </tr>
                            </table>
                            <!-- END Shell -->
                        </td>
                    </tr>
                </table>
                <!-- END Main -->
            </td>
        </tr>
    </table>
</body>
</html>