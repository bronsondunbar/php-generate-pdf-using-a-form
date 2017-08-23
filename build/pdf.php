<?php

require "includes/PHPMailerAutoload.php";
require "includes/fpdf.php";

/* Global */

$captcha = "";
$captchaError = "";

$userName = "";
$userEmail = "";
$userAge = "";
$userAddress = "";
$userBefore = "";
$userComments = "";

$rootFolder = "http://" . $_SERVER['HTTP_HOST'] . "/components/php-generate-pdf-using-a-form/";
$userPage = "";
$pageMessage = "";
$emailCopy = "";

/* Get values from form */

if(isset($_POST["g-recaptcha-response"])){

	$captcha=$_POST["g-recaptcha-response"];

}

if (empty($_POST["name"])) {

	$responseData["nameError"] = "<i class='fa fa-times' aria-hidden='true'></i> Please type your name.";

} else {

	$userName = trim($_POST["name"]);
	$userName = ucwords($userName);

}

if (empty($_POST["email"])) {

	$responseData["emailError"] = "<i class='fa fa-times' aria-hidden='true'></i> Please type your email.";

} else {

	$userEmail = trim($_POST["email"]);

}

if (empty($_POST["age"])) {

	$responseData["ageError"] = "<i class='fa fa-times' aria-hidden='true'></i> Please type your date of birth.";

} else {

	$userAge = trim($_POST["age"]);

}

if (empty($_POST["address"])) {

	$responseData["addressError"] = "<i class='fa fa-times' aria-hidden='true'></i> Please type your address.";

} else {

	$userAddress = trim($_POST["address"]);

}

$userBefore = trim($_POST["before"]);
$userComments = trim($_POST["comments"]);

if ($userComments != "") {
	$userComments = "<tr>" . "\n"
					. "<td align='center'>" . $userComments . "</td>" . "\n"
					. "</tr>" . "\n";
} else {
	$userComments = "<tr>" . "\n"
					. "<td align='center'>No comments.</td>" . "\n"
					. "</tr>" . "\n";
}

$secret = "PRIVATE_KEY";
$ip = $_SERVER['REMOTE_ADDR'];
$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha."&remoteip=".$ip);
$responseKeys = json_decode($response,true);

if(intval($responseKeys["success"]) !== 1) {

	$responseData["captchaError"] = "<i class='fa fa-times' aria-hidden='true'></i> Please verify that you are human.";

} else {

	$fileName = str_replace(" ", "-", $userName);
	$fileName = strtolower($fileName);
	$fileName = $fileName . "-" . date("Y-m-d-H-i-s");
	$filePath = $rootFolder . $fileName . ".pdf";

	function hex2dec($couleur = "#000000"){
	    $R = substr($couleur, 1, 2);
	    $rouge = hexdec($R);
	    $V = substr($couleur, 3, 2);
	    $vert = hexdec($V);
	    $B = substr($couleur, 5, 2);
	    $bleu = hexdec($B);
	    $tbl_couleur = array();
	    $tbl_couleur["R"]=$rouge;
	    $tbl_couleur["G"]=$vert;
	    $tbl_couleur["B"]=$bleu;
	    return $tbl_couleur;
	}

	function px2mm($px){
	    return $px*25.4/72;
	}

	function txtentities($html){
	    $trans = get_html_translation_table(HTML_ENTITIES);
	    $trans = array_flip($trans);
	    return strtr($html, $trans);
	}

	class PDF extends FPDF
	{
		protected $B;
		protected $I;
		protected $U;
		protected $HREF;
		protected $fontList;
		protected $issetfont;
		protected $issetcolor;

		function __construct($orientation="P", $unit="mm", $format="A4")
		{
		    parent::__construct($orientation,$unit,$format);

		    $this->B=0;
		    $this->I=0;
		    $this->U=0;
		    $this->HREF="";

		    $this->tableborder=0;
		    $this->tdbegin=false;
		    $this->tdwidth=0;
		    $this->tdheight=0;
		    $this->tdalign="L";
		    $this->tdbgcolor=false;

		    $this->oldx=0;
		    $this->oldy=0;

		    $this->fontlist=array("arial","times","courier","helvetica","symbol");
		    $this->issetfont=false;
		    $this->issetcolor=false;
		}

		function WriteHTML($html)
		{
		    $html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote><hr><td><tr><table><sup>");
		    $html=str_replace("\n","",$html);
		    $html=str_replace("\t","",$html);
		    $a=preg_split("/<(.*)>/U",$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		    foreach($a as $i=>$e)
		    {
		        if($i%2==0)
		        {
		            if($this->HREF)
		                $this->PutLink($this->HREF,$e);
		            elseif($this->tdbegin) {
		                if(trim($e)!="" && $e!="&nbsp;") {
		                    $this->Cell($this->tdwidth,$this->tdheight,$e,$this->tableborder,"",$this->tdalign,$this->tdbgcolor);
		                }
		                elseif($e=="&nbsp;") {
		                    $this->Cell($this->tdwidth,$this->tdheight,"",$this->tableborder,"",$this->tdalign,$this->tdbgcolor);
		                }
		            }
		            else
		                $this->Write(5,stripslashes(txtentities($e)));
		        }
		        else
		        {
		            if($e[0]=="/")
		                $this->CloseTag(strtoupper(substr($e,1)));
		            else
		            {
		                $a2=explode(" ",$e);
		                $tag=strtoupper(array_shift($a2));
		                $attr=array();
		                foreach($a2 as $v)
		                {
		                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
		                        $attr[strtoupper($a3[1])]=$a3[2];
		                }
		                $this->OpenTag($tag,$attr);
		            }
		        }
		    }
		}

		function OpenTag($tag, $attr)
		{
		    switch($tag){

		        case "SUP":
		            if( !empty($attr["SUP"]) ) {
		                $this->SetFont("","",6);
		                $this->Cell(2,2,$attr["SUP"],0,0,"L");
		            }
		            break;

		        case "TABLE":
		            if( !empty($attr["BORDER"]) ) $this->tableborder=$attr["BORDER"];
		            else $this->tableborder=0;
		            break;
		        case "TR":
		            break;
		        case "TD":
		            if( !empty($attr["WIDTH"]) ) $this->tdwidth=($attr["WIDTH"]/4);
		            else $this->tdwidth=760/4;
		            if( !empty($attr["HEIGHT"]) ) $this->tdheight=($attr["HEIGHT"]/6);
		            else $this->tdheight=6;
		            if( !empty($attr["ALIGN"]) ) {
		                $align=$attr["ALIGN"];        
		                if($align=="LEFT") $this->tdalign="L";
		                if($align=="CENTER") $this->tdalign="C";
		                if($align=="RIGHT") $this->tdalign="R";
		                if($align=="left") $this->tdalign="L";
		                if($align=="center") $this->tdalign="C";
		                if($align=="right") $this->tdalign="R";
		            }
		            else $this->tdalign="L";
		            if( !empty($attr["BGCOLOR"]) ) {
		                $coul=hex2dec($attr["BGCOLOR"]);
		                    $this->SetFillColor($coul["R"],$coul["G"],$coul["B"]);
		                    $this->tdbgcolor=true;
		                }
		            $this->tdbegin=true;
		            break;

		        case "HR":
		            if( !empty($attr["WIDTH"]) )
		                $Width = $attr["WIDTH"];
		            else
		                $Width = $this->w - $this->lMargin-$this->rMargin;
		            $x = $this->GetX();
		            $y = $this->GetY();
		            $this->SetLineWidth(0.2);
		            $this->Line($x,$y,$x+$Width,$y);
		            $this->SetLineWidth(0.2);
		            $this->Ln(1);
		            break;
		        case "STRONG":
		            $this->SetStyle("B",true);
		            break;
		        case "EM":
		            $this->SetStyle("I",true);
		            break;
		        case "B":
		        case "I":
		        case "U":
		            $this->SetStyle($tag,true);
		            break;
		        case "A":
		            $this->HREF=$attr["HREF"];
		            break;
		        case "IMG":
		            if(isset($attr["SRC"]) && (isset($attr["WIDTH"]) || isset($attr["HEIGHT"]))) {
		                if(!isset($attr["WIDTH"]))
		                    $attr["WIDTH"] = 0;
		                if(!isset($attr["HEIGHT"]))
		                    $attr["HEIGHT"] = 0;
		                $this->Image($attr["SRC"], $this->GetX(), $this->GetY(), px2mm($attr["WIDTH"]), px2mm($attr["HEIGHT"]));
		            }
		            break;
		        case "BLOCKQUOTE":
		        case "BR":
		            $this->Ln(5);
		            break;
		        case "P":
		            $this->Ln(10);
		            break;
		        case "FONT":
		            if (isset($attr["COLOR"]) && $attr["COLOR"]!="") {
		                $coul=hex2dec($attr["COLOR"]);
		                $this->SetTextColor($coul["R"],$coul["G"],$coul["B"]);
		                $this->issetcolor=true;
		            }
		            if (isset($attr["FACE"]) && in_array(strtolower($attr["FACE"]), $this->fontlist)) {
		                $this->SetFont(strtolower($attr["FACE"]));
		                $this->issetfont=true;
		            }
		            if (isset($attr["FACE"]) && in_array(strtolower($attr["FACE"]), $this->fontlist) && isset($attr["SIZE"]) && $attr["SIZE"]!="") {
		                $this->SetFont(strtolower($attr["FACE"]),"",$attr["SIZE"]);
		                $this->issetfont=true;
		            }
		            break;
		    }
		}

		function CloseTag($tag)
		{
		    if($tag=="SUP") {
		    }

		    if($tag=="TD") {
		        $this->tdbegin=false;
		        $this->tdwidth=0;
		        $this->tdheight=0;
		        $this->tdalign="L";
		        $this->tdbgcolor=false;
		    }
		    if($tag=="TR") {
		        $this->Ln();
		    }
		    if($tag=="TABLE") {
		        $this->tableborder=0;
		    }

		    if($tag=="STRONG")
		        $tag="B";
		    if($tag=="EM")
		        $tag="I";
		    if($tag=="B" || $tag=="I" || $tag=="U")
		        $this->SetStyle($tag,false);
		    if($tag=="A")
		        $this->HREF="";
		    if($tag=="FONT"){
		        if ($this->issetcolor==true) {
		            $this->SetTextColor(0);
		        }
		        if ($this->issetfont) {
		            $this->SetFont("arial");
		            $this->issetfont=false;
		        }
		    }
		}

		function SetStyle($tag, $enable)
		{
		    $this->$tag+=($enable ? 1 : -1);
		    $style="";
		    foreach(array("B","I","U") as $s) {
		        if($this->$s>0)
		            $style.=$s;
		    }
		    $this->SetFont("",$style);
		}

		function PutLink($URL, $txt)
		{
		    $this->SetTextColor(0,0,255);
		    $this->SetStyle("U",true);
		    $this->Write(5,$txt,$URL);
		    $this->SetStyle("U",false);
		    $this->SetTextColor(0);
		}

		function Header()
		{
			$this->Ln(20);
		    $this->Image("http://bronsondunbar.com/components/php-generate-pdf-using-a-form/assets/imgs/logo.png", 45, 6, 120);
		    $this->Ln(1);
		    $this->SetFont("Arial", "", 10);
		    $this->SetTextColor(17, 64, 90);
		    $this->Cell(0, 10, "Tel: (012) 345 6789 | Email: sales@beats.com", 0, 0, "C");
		    $this->Ln(5);
		    $this->Cell(0, 10, "1234 Street, City", 0, 0, "C");
		    $this->Ln(10);
		}

		function Footer()
		{
		    $this->SetY(-15);
		    $this->SetFont("Arial", "", 8);
		    $this->Cell(0, 10, "www.company.com", 0, 0, "C");
		    $this->Ln(5);
		    $this->Cell(0, 10, "Page ".$this->PageNo()." of {nb}", 0, 0, "C");
		}

	}

	$userSubmission = 	"<table>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'><strong>Name:</strong></td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'>" . $userName . "</td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'><strong>Email:</strong></td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'>" . $userEmail . "</td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'><strong>Date of birth:</strong></td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'>" . $userAge . "</td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'><strong>Address:</strong></td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'>" . $userAddress . "</td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'><strong>Have you used us before?</strong></td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'>" . $userBefore . "</td>" . "\n"
					. "</tr>" . "\n"
					. "<tr>" . "\n"
					. "<td align='center'><strong>Comments:</strong></td>" . "\n"
					. "</tr>" . "\n"
					. $userComments
					. "</table>";

	$privacyPolicy = "<p>We recognize that your privacy is important. This document outlines the types of personal information we receive and collect when you use company.com, as well as some of the steps we take to safeguard information. We hope this will help you make an informed decision about sharing personal information with us.</p>
<p>company.com strives to maintain the highest standards of decency, fairness and integrity in all our operations. Likewise, we are dedicated to protecting our customers', consumers' and online visitors' privacy on our website.</p>
<p><strong>Personal Information</strong></p>
<p>company.com collects personally identifiable information from the visitors to our website only on a voluntary basis. Personal information collected on a voluntary basis may include name, postal address, email address, company name and telephone number.</p>
<p>This information is collected if you request information from us, participate in a contest or sweepstakes, and sign up to join our email list or request some other service or information from us. The information collected is internally reviewed, used to improve the content of our website, notify our visitors of updates, and respond to visitor inquiries.</p>
<p>Once information is reviewed, it is discarded or stored in our files. If we make material changes in the collection of personally identifiable information we will inform you by placing a notice on our site. Personal information received from any visitor will be used only for internal purposes and will not be sold or provided to third parties.</p>
<p><strong>Use of Cookies</strong></p>
<p>We may use cookies to help you personalize your online experience. Cookies are identifiers that are transferred to your computer's hard drive through your Web browser to enable our systems to recognize your browser. The purpose of a cookie is to tell the Web server that you have returned to a specific page. For example, if you personalize the sites pages, or register with any of our site's services, a cookie enables company.com to recall your specific information on subsequent visits.</p>
<p>You have the ability to accept or decline cookies by modifying your Web browser; however, if you choose to decline cookies, you may not be able to fully experience the interactive features of the site.</p>
<p><strong>Children's Online Privacy Protection Act</strong></p>
<p>This website is directed to adults; it is not directed to children under the age of 13. We operate our site in compliance with the Children's Online Privacy Protection Act, and will not knowingly collect or use personal information from anyone under 13 years of age.</p>
<p><strong>Non-Personal Information</strong></p>
<p>In some cases, we may collect information about you that is not personally identifiable. We use this information, which does not identify individual users, to analyze trends, to administer the site, to track users' movements around the site and to gather demographic information about our user base as a whole. The information collected is used solely for internal review and not shared with other organizations for commercial purposes.</p>
<p><strong>Release of Information</strong></p>
<p>If company.com is sold, the information we have obtained from you through your voluntary participation in our site may transfer to the new owner as a part of the sale in order that the service being provided to you may continue. In that event, you will receive notice through our website of that change in control and practices, and we will make reasonable efforts to ensure that the purchaser honors any opt-out requests you might make of us.</p>
<p><strong>How You Can Correct or Remove Information</strong></p>
<p>We provide this privacy policy as a statement to you of our commitment to protect your personal information. If you have submitted personal information through our website and would like that information deleted from our records or would like to update or correct that information, please click on this link and/or use our Contact Me page.</p>
<p><strong>Updates and Effective Date</strong></p>
<p>company.com reserves the right to make changes in this policy. If there is a material change in our privacy practices, we will indicate on our site that our privacy practices have changed and provide a link to the new privacy policy. We encourage you to periodically review this policy so that you will know what information we collect and how we use it.</p>
<p><strong>Agreeing to Terms</strong></p>
<p>If you do not agree to company.com's Privacy Policy as posted here on this website, please do not use this site or any services offered by this site.</p>
<p>Your use of this site indicates acceptance of this privacy policy.</p>";

	$pdf = new PDF();

	$pdf->AliasNbPages();
	$pdf->AddPage();
	$pdf->SetFont("Arial", "", 16);
    $pdf->SetTextColor(41, 183, 234);
    $pdf->Cell(80);
    $pdf->Cell(30, 10, "Submission from " . $userName . " sent on " . date("d-m-Y"), 0, 0, "C");
    $pdf->Ln(10);
	$pdf->SetFont("Arial", "", 14);
	$pdf->SetTextColor(41, 183, 234);
    $pdf->Cell(80);
    $pdf->Cell(30, 10, "Content:", 0, 0, "C");
    $pdf->Ln(10);
    $pdf->SetFont("Arial", "", 10);
    $pdf->SetTextColor(65, 64, 66);
	$pdf->WriteHTML($userSubmission);
	$pdf->AddPage();
	$pdf->SetFont("Arial", "", 16);
    $pdf->SetTextColor(41, 183, 234);
    $pdf->Cell(80);
    $pdf->Cell(30, 10, "Privacy Policy", 0, 0, "C");
    $pdf->Ln(5);
    $pdf->SetFont("Arial", "", 10);
    $pdf->SetTextColor(65, 64, 66);
	$pdf->WriteHTML($privacyPolicy);
	$pdf->Output($fileName . ".pdf", "F");

	$emailCopy = $_POST["copy"];

	if ($emailCopy == "Yes") {

	    $sendEmail = new PHPMailer;

	    $sendEmail->SMTPDebug = 3;

	    $sendEmail->setFrom("bronson@bronsondunbar.com", "Submission");
	    $sendEmail->addAddress($userEmail, "Submission");
	    $sendEmail->Subject = "Submission";
	    $sendEmail->Body = "Please find your copy of your submission attached.";
	    $sendEmail->AltBody = "To view the message, please use an HTML compatible email viewer!";
	    $sendEmail->IsHTML(true);
	    $sendEmail->addAttachment($fileName . ".pdf");

	    if(!$sendEmail->send()) {

	    	$responseData["emailSentError"] = "<i class='fa fa-check' aria-hidden='true'></i> Page has been created. Could not send link to email address. <br />" . $mail->ErrorInfo;

	    } else {
	      
	      $responseData["pageSuccess"] = "<i class='fa fa-check' aria-hidden='true'></i> Page has been created. <a href='" . $filePath . "' target='_blank'>Click here</a> to view or follow the link in the email.";

	    }

	  } else {

	    $responseData["pageSuccess"] = "<i class='fa fa-check' aria-hidden='true'></i> Page has been created. <a href='" . $filePath . "' target='_blank'>Click here</a> to view.";

	  }


}

echo json_encode($responseData);

?>