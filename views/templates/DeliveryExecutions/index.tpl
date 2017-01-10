<?php
use oat\tao\helpers\Template;
?>

<link rel="stylesheet" href="<?= Template::css('../js/lib/c3js/c3.css', 'tao') ?>" />

<div class="flex-container-full">
    <header>
        <h2><?= get_data("title") ?></h2>
    </header>
    
    <header>
        <h3><?= __('Current status') ?></h3>
        <p><?= get_data('status') ?></p>
    </header>
    
    <?php if (get_data('startDate') && get_data('endDate')) : ?>
        <header>
            <h3><?= __('Time Frame') ?></h3>
            <p> 
                <?= tao_helpers_Date::displayeDate(get_data('startDate'), tao_helpers_Date::FORMAT_VERBOSE) ?> 
                --
                <?= tao_helpers_Date::displayeDate(get_data('endDate'), tao_helpers_Date::FORMAT_VERBOSE) ?>
            </p>
        </header>
    <?php endif; ?>
    
    <div class="data-container-wrapper flex-container-remainer">
        <header>
            <h3><?= __('Started Delivery Executions') ?></h3>
        </header>
        
        <div class="delivery-executions-progress">
            <progress max="<?= get_data('possible') ?>" value="<?= get_data("countExecutions") ?>" class="pb-de" title="<?= get_data("countExecutions") ?> <?= __('Executions') ?>">
                <div class="progress-bar">
                    <span style="width: <?= get_data('percent') ?>%"><?= get_data('limit') ?></span>
                </div>
            </progress>
            <span style="position: relative; left: 30px; top: -23px" title="<?= get_data("countExecutions") ?> <?= __('Executions') ?>"><?= get_data("countExecutions") ?></span>
            <p style="width: 100%; top: -5px" data-value="<?= get_data('limit') ?>">0</p>
        </div>

        <header>
            <h3><?= __('Users activity') ?></h3>
        </header>
        <div class="row">
            <div class="col-2">
                <div class="connected-users">
                    <b><?= get_data("connectedUsers") ?></b><br>
                    <span><?= __('Connected Users') ?></span>
                </div>
            </div>
            <div class="col-7">
                <div id="barChar" data-delivery="<?= get_data('deliveryUri') ?>"></div>
            </div>
        </div>
        
    </div>
</div>

<?php
Template::inc('footer.tpl', 'tao');
?>
