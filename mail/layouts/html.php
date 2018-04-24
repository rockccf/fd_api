<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
        <style>
            hr {
                display: block;
                height: 3px;
                border: 0;
                border-top: 1px solid #f15824;
                background-color: #f15824;
                padding: 0;
            }
        </style>
        <?php $this->head() ?>
        <title>HorecaBid.com</title>
    </head>
    <body style="margin: 0; padding: 0;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-top:25px;">
                <!-- Header Start -->
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="750" style="border-collapse: collapse;">
                    <tr>
                        <td>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="730" style="border-collapse: collapse;">
                                <tr>
                                    <td>
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="450" style="border-collapse: collapse;">
                                            <!-- logo -->
                                            <tr>
                                                <td align="left">
                                                    <a href="<?= Yii::getAlias('@landingPageUrl') ?>">
                                                        <img src="<?= Url::to('images/logo.png',true); ?>" width="80" alt="HorecaBid.com" style="display: block;"/>
                                                    </a>
                                                </td>
                                            </tr>
                                            <!-- company slogan -->
                                            <tr>
                                                <td width="100%" align="left" style="font-size: 12px; line-height: 18px; font-family:helvetica, Arial, sans-serif; color:#999999;">
                                                    Buy & Sell Smarter<br/>
                                                    A Network of Real Buyers and Verified Sellers
                                                </td>
                                            </tr>
                                            <!-- Space -->
                                            <tr><td style="font-size: 0; line-height: 0;" height="15">&nbsp;</td></tr>
                                        </table>
                                        <table align="right" border="0" cellpadding="0" cellspacing="0" width="250" style="border-collapse: collapse;">
                                            <tr>
                                                <td height="75" style="text-align: right; vertical-align: middle;">
                                                    <a href="<?= Yii::getAlias('@landingPageUrl') ?>/main/aboutUs" style="font-family:helvetica, Arial, sans-serif; color: #666666; font-size: 12px; font-weight: bold; text-decoration: none;">ABOUT US</a> &nbsp;&nbsp;
                                                    <a href="<?= Yii::getAlias('@landingPageUrl') ?>/main/contactUs" style="font-family:helvetica, Arial, sans-serif; color: #666666; font-size: 12px; font-weight: bold; text-decoration: none;">CONTACT US</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <!-- Header End -->

                <!-- Section Start -->
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="750" style="border-collapse: collapse;">
                    <tr>
                        <td>
                            <table align="center" bgcolor="#f15824" border="0" cellpadding="0" cellspacing="0" width="730" style="border-collapse: collapse;">
                                <tr><td style="font-size: 0; line-height: 0;" height="5">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <!-- Section End -->
            </td>
        </tr>
    </table>
    <?php $this->beginBody() ?>
    <?= $content ?>
    <!-- Section Start -->
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="750" style="border-collapse: collapse;">
        <tr>
            <td>
                <table align="center" bgcolor="#f15824" border="0" cellpadding="0" cellspacing="0" width="730" style="border-collapse: collapse;">
                    <tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Section End -->
    <!-- Footer Start -->
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="750" style="border-collapse: collapse;">
        <tr>
            <td>
                <table bgcolor="#f7f7f7" align="center" border="0" cellpadding="0" cellspacing="0" width="730" style="border-collapse: collapse;">
                    <tr>
                        <td>
                            <!-- Space -->
                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr><td style="font-size: 0; line-height: 0;" bgcolor="#DDDDDD" height="1">&nbsp;</td></tr>
                                <tr><td style="font-size: 0; line-height: 0;" height="30">&nbsp;</td></tr>
                            </table>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="690" style="border-collapse: collapse;">
                                <tr>
                                    <td>
                                        <!-- First Column -->
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="320" style="border-collapse: collapse;">
                                            <tr>
                                                <td>
                                                    <a href="<?= Yii::getAlias('@landingPageUrl') ?>">
                                                        <img src="<?= Url::to('images/logo.png',true); ?>" width="80" alt="Logo" style="display: block;"/>
                                                    </a>
                                                </td>
                                            </tr>
                                            <!-- Space -->
                                            <tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
                                            <tr>
                                                <td style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
                                                    Reduce cost. Save Time. Increase efficiency.<br/><br/>
                                                    Efficiently streamline your tendering process.
                                                </td>
                                            </tr>
                                            <!-- Space -->
                                            <tr><td style="font-size: 0; line-height: 0;" height="15">&nbsp;</td></tr>
                                            <tr>
                                                <td>
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="55" style="border-collapse: collapse;">
                                                        <tr>
                                                            <td>
                                                                <a href="https://www.facebook.com/horecabid">
                                                                    <img src="<?= Url::to('images/facebook-icon.png',true); ?>" height="40" width="39" alt="Facebook" style="display: block;" />
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="55" style="border-collapse: collapse;">
                                                        <tr>
                                                            <td>
                                                                <a href="https://twitter.com/horecabid">
                                                                    <img src="<?= Url::to('images/twitter-icon.png',true); ?>" height="40" width="39" alt="Twitter" style="display: block;" />
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="55" style="border-collapse: collapse;">
                                                        <tr>
                                                            <td>
                                                                <a href="https://www.linkedin.com/company/horecabid.com/">
                                                                    <img src="<?= Url::to('images/linkedin-icon.png',true); ?>" height="40" width="39" alt="Linkedin" style="display: block;" />
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Gutter 20px -->
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="50" style="border-collapse: collapse;">
                                            <tr>
                                                <td>
                                                    &nbsp;
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- Second Column -->
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="320" style="border-collapse: collapse;">
                                            <!-- Space -->
                                            <tr><td style="font-size: 0; line-height: 0;" height="69">&nbsp;</td></tr>
                                            <tr>
                                                <td width="22">
                                                    <img src="<?= Url::to('images/marker-icon.png',true); ?>" alt="location" />
                                                </td>
                                                <td style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">HorecaBid Sdn. Bhd.</td>
                                            </tr>
                                            <!-- Space -->
                                            <tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>
                                            <tr>
                                                <td width="22">
                                                    <img src="<?= Url::to('images/phone-icon.png',true); ?>" alt="location" />
                                                </td>
                                                <td style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">+603-7628 4503</td>
                                            </tr>
                                            <!-- Space -->
                                            <tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>
                                            <tr>
                                                <td width="22">
                                                    <img src="<?= Url::to('images/fax-icon.png',true); ?>" alt="location" />
                                                </td>
                                                <td style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">1700-81-3933</td>
                                            </tr>
                                            <!-- Space -->
                                            <tr><td style="font-size: 0; line-height: 0;" height="10">&nbsp;</td></tr>
                                            <tr>
                                                <td width="22">
                                                    <img src="<?= Url::to('images/mail-icon.png',true); ?>" alt="location" />
                                                </td>
                                                <td>
                                                    <a style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif; text-decoration:none;" href="mailto:support@horecabid.com">support@horecabid.com</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- Space -->
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                                <tr><td style="font-size: 0; line-height: 0;" height="30">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
    <!-- Footer End -->

    <!-- Subfooter Start -->
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="750" style="border-collapse: collapse;">
        <tr>
            <td>
                <table bgcolor="#e7e7e7" align="center" border="0" cellpadding="0" cellspacing="0" width="730" style="border-collapse: collapse;">
                    <tr>
                        <td>
                            <!-- Space -->
                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr><td style="font-size: 0; line-height: 0;" bgcolor="#DDDDDD" height="1">&nbsp;</td></tr>
                                <tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
                            </table>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="690" style="border-collapse: collapse;">
                                <tr>
                                    <td align="center" style="color: #999999; font-size: 14px; line-height: 18px; font-weight: normal; font-family: helvetica, Arial, sans-serif;">
                                        Copyright © <?= date("Y") ?> HorecaBid.com. All Rights Reserved.
                                    </td>
                                </tr>
                            </table>
                            <!-- Space -->
                            <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                                <tr><td style="font-size: 0; line-height: 0;" height="20">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
    <!-- Subfooter End -->
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>