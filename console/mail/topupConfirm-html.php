<?php
use yii\helpers\Html;

?>
<div lang="EN-US" link="blue" vlink="purple">
    <h3 style="line-height:16.5pt;background:white">
        <span
            style="font-size:11.0pt;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;color:black;font-weight:normal">Your Payment has been sent.<u></u><u></u></span>
    </h3>

    <h3 style="line-height:16.5pt;background:white"><span
            style="font-size:11.0pt;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;color:black;font-weight:normal">You just put someone’s phone back in action.<br>We’ve copied your latest Payment details below in case you need them.<u></u><u></u></span>
    </h3>

    <h3 style="line-height:16.5pt;background:white"><span
            style="font-size:11.0pt;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;color:black;font-weight:normal">Details:<u></u><u></u></span>
    </h3>

    <div align="center">
        <table border="0" cellpadding="0" width="100%" style="width:100.0%">
            <tbody>
            <tr>
                <td style="padding:8.75pt .75pt .75pt .75pt">
                    <table border="0" cellpadding="0" align="left" width="40%" style="width:40.0%">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black">Transaction ID</span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    
                    <table border="0" cellpadding="0" align="left" width="60%" style="width:60.0%;min-width:200px">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black"><?php echo $invoiceNo ?></span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p><p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
            </tr>
            <tr>
                <td style="padding:8.75pt .75pt .75pt .75pt">
                    <table border="0" cellpadding="0" align="left" width="40%" style="width:40.0%">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black">Date &amp; Time</span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    
                    <table border="0" cellpadding="0" align="left" width="60%" style="width:60.0%;min-width:200px">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black"><?php echo $dateFormated ?></span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p><p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
            </tr>
            <tr>
                <td style="padding:8.75pt .75pt .75pt .75pt">
                    <table border="0" cellpadding="0" align="left" width="40%" style="width:40.0%">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black">Amount Sent:</span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    
                    <table border="0" cellpadding="0" align="left" width="60%" style="width:60.0%;min-width:200px">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black">USD <?php echo $amountFormated ?></span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p><p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
            </tr>

            <tr>
                <td style="padding:8.75pt .75pt .75pt .75pt">
                    <table border="0" cellpadding="0" align="left" width="40%" style="width:40.0%">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black">Account</span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    
                    <table border="0" cellpadding="0" align="left" width="60%" style="width:60.0%;min-width:200px">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black"><?php echo $phoneNumberFormated ?></span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p><p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
            </tr>
            <tr>
                <td style="padding:8.75pt .75pt .75pt .75pt">
                    <table border="0" cellpadding="0" align="left" width="40%" style="width:40.0%">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black">Country of Recipient</span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    
                    <table border="0" cellpadding="0" align="left" width="60%" style="width:60.0%;min-width:200px">
                        <tbody>
                        <tr>
                            <td style="padding:8.75pt .75pt .75pt .75pt"><p class="MsoNormal"><span style="color:black">Haiti</span><span
                                        style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p><p class="MsoNormal"><span style="font-size:12.0pt;color:black"><u></u><u></u></span></p></td>
            </tr>
            </tbody>
        </table>
    </div>
    <h3 style="line-height:16.5pt;background:white"><span
            style="font-size:11.0pt;font-family:&quot;Calibri&quot;,&quot;sans-serif&quot;;color:black;font-weight:normal">We do everything we can to deliver your Payment instantly. However, now and then the process can take a little longer so please be patient. If you have any questions, <span
                style="color:black"><a href="http://paymentonline.natcom.com.ht/site/help" target="_blank">please visit our&nbsp;FAQ
                    section.</a></span><u></u><u></u></span></h3>

    </div>

<table cellspacing="0" cellpadding="6" border="0" bgcolor="#ea670b">
    <tbody>
    <tr>
        <td align="left" valign="top">
            <a href="http://paymentonline.natcom.com.ht" style="color: white;text-decoration: none;font-size:11.0pt;font-family:'Calibri','sans-serif';font-weight:normal">SEND MORE PAYMENT</a>
        </td>
    </tr>
    </tbody>
</table>
