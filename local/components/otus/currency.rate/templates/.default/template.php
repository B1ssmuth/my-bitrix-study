<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); ?>
<div style="padding: 15px; border: 1px solid #000; display: inline-block;">
    Текущий курс <b><?=$arResult['CURRENCY']?></b>: 
    <span style="color: green; font-weight: bold;"><?=number_format($arResult['AMOUNT'], 2)?> руб.</span>
</div>