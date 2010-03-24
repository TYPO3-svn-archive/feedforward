<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_feedforward_pi1 = < plugin.tx_feedforward_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_feedforward_pi1.php','_pi1','list_type',1);

$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_feedforward_urleval1'] = 'EXT:feedforward/pi1/class.tx_feedforward_urleval1.php';

?>