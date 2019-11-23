<?php
/**
 * @package     VikRentItems
 * @subpackage  com_vikrentitems
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

$priceid = $this->priceid;
$place = $this->place;
$returnplace = $this->returnplace;
$elemid = $this->elemid;
$days = $this->days;
$pickup = $this->pickup;
$release = $this->release;
$copts = $this->copts;

$action = 'index.php?option=com_user&amp;task=login';

$pitemid = VikRequest::getString('Itemid', '', 'request');

if (!empty($elemid) && !empty($pickup) && !empty($release)) {
	$chosenopts = "";
	if (is_array($copts) && @count($copts) > 0) {
		foreach ($copts as $idopt => $quanopt) {
			$chosenopts .= "&optid".$idopt."=".$quanopt;
		}
	}
	$goto = "index.php?option=com_vikrentitems&task=oconfirm&priceid=".$priceid."&place=".$place."&returnplace=".$returnplace."&elemid=".$elemid."&days=".$days."&pickup=".$pickup."&release=".$release.(!empty($chosenopts) ? $chosenopts : "").(!empty($pitemid) ? "&Itemid=".$pitemid : "");
	$goto = VikRentItems::getLoginReturnUrl($goto);
} else {
	//User Reservations page
	$goto = "index.php?option=com_vikrentitems&view=userorders";
	$goto = VikRentItems::getLoginReturnUrl($goto);
}

$return_url = base64_encode($goto);

?>

<script language="JavaScript" type="text/javascript">
function checkVrcReg() {
	var vrvar = document.vrireg;
	if (!vrvar.name.value.match(/\S/)) {
		document.getElementById('vrifname').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vrifname').style.color='';
	}
	if (!vrvar.lname.value.match(/\S/)) {
		document.getElementById('vriflname').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vriflname').style.color='';
	}
	if (!vrvar.email.value.match(/\S/)) {
		document.getElementById('vrifemail').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vrifemail').style.color='';
	}
	if (!vrvar.username.value.match(/\S/)) {
		document.getElementById('vrifusername').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vrifusername').style.color='';
	}
	if (!vrvar.password.value.match(/\S/)) {
		document.getElementById('vrifpassword').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vrifpassword').style.color='';
	}
	if (!vrvar.confpassword.value.match(/\S/)) {
		document.getElementById('vrifconfpassword').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vrifconfpassword').style.color='';
	}
	return true;
}
</script>

<div class="loginregistercont">
		
	<div class="registerblock">
	<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" method="post" name="vrireg" onsubmit="return checkVrcReg();">
	<h3><?php echo JText::_('VRREGSIGNUP'); ?></h3>
	<table valign="top">
		<tr><td align="right"><span id="vrifname"><?php echo JText::_('VRREGNAME'); ?></span></td><td><input type="text" name="name" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right"><span id="vriflname"><?php echo JText::_('VRREGLNAME'); ?></span></td><td><input type="text" name="lname" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right"><span id="vrifemail"><?php echo JText::_('VRREGEMAIL'); ?></span></td><td><input type="text" name="email" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right"><span id="vrifusername"><?php echo JText::_('VRREGUNAME'); ?></span></td><td><input type="text" name="username" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right"><span id="vrifpassword"><?php echo JText::_('VRREGPWD'); ?></span></td><td><input type="password" name="password" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right"><span id="vrifconfpassword"><?php echo JText::_('VRREGCONFIRMPWD'); ?></span></td><td><input type="password" name="confpassword" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right">&nbsp;</td><td><input type="submit" value="<?php echo JText::_('VRREGSIGNUPBTN'); ?>" class="booknow" name="submit" /></td></tr>
	</table>
	<input type="hidden" name="priceid" value="<?php echo $priceid; ?>" />
	<input type="hidden" name="place" value="<?php echo $place; ?>" />
	<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>" />
	<input type="hidden" name="elemid" value="<?php echo $elemid; ?>" />
	<input type="hidden" name="days" value="<?php echo $days; ?>" />
	<input type="hidden" name="pickup" value="<?php echo $pickup; ?>" />
	<input type="hidden" name="release" value="<?php echo $release; ?>" />
	<?php
	if (is_array($copts) && @count($copts) > 0) {
		foreach ($copts as $idopt => $quanopt) {
			?>
	<input type="hidden" name="optid<?php echo $idopt; ?>" value="<?php echo $quanopt; ?>" />
			<?php
		}
	}
	?>
	<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>" />
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="register" />
	</form>
	</div>
<?php
jimport('joomla.version');
$version = new JVersion();
$jv=$version->getShortVersion();
if (version_compare($jv, '1.6.0') < 0) {
	$validate = JUtility::getToken();
	//Joomla 1.5
?>
	<div class="loginblock">
	<form action="<?php echo $action; ?>" method="post">
	<h3><?php echo JText::_('VRREGSIGNIN'); ?></h3>
	<table valign="top">
		<tr><td align="right"><?php echo JText::_('VRREGUNAME'); ?></td><td><input type="text" name="username" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right"><?php echo JText::_('VRREGPWD'); ?></td><td><input type="password" name="passwd" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right">&nbsp;</td><td><input type="submit" value="<?php echo JText::_('VRREGSIGNINBTN'); ?>" class="booknow" name="Login" /></td></tr>
	</table>
	<input type="hidden" name="remember" id="remember" value="yes" />
	<input type="hidden" value="login" name="op2" />
	<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
	<input type="hidden" name="<?php echo $validate; ?>" value="1" />
	</form>
	</div>
<?php
} else {
	//joomla 3.0
?>
	<div class="loginblock">
	<form action="index.php?option=com_users" method="post">
	<h3><?php echo JText::_('VRREGSIGNIN'); ?></h3>
	<table valign="top">
		<tr><td align="right"><?php echo JText::_('VRREGUNAME'); ?></td><td><input type="text" name="username" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right"><?php echo JText::_('VRREGPWD'); ?></td><td><input type="password" name="password" value="" size="20" class="vriinput"/></td></tr>
		<tr><td align="right">&nbsp;</td><td><input type="submit" value="<?php echo JText::_('VRREGSIGNINBTN'); ?>" class="booknow" name="Login" /></td></tr>
	</table>
	<input type="hidden" name="remember" id="remember" value="yes" />
	<input type="hidden" name="return" value="<?php echo $return_url; ?>" />
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<?php echo JHtml::_('form.token'); ?>
	</form>
	</div>
<?php
}
?>
		
</div>
