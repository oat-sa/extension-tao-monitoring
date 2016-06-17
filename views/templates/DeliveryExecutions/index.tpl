<?php
use oat\tao\helpers\Template;
?>

<link rel="stylesheet" href="<?= Template::css('delivery-execution.css') ?>" />

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
            <h3><?= __('Submitted tests') ?></h3>
        </header>
        
        <?php
        $possible = get_data("possibleExecutionsCount") ? get_data("possibleExecutionsCount") : get_data("countExecutions") * 2;
        $limit = get_data("possibleExecutionsCount") ? get_data("possibleExecutionsCount") . ' ' . __('Total Expected') : __('Unlimited');
        
        $percent = 0;
        if ($possible) {
            $percent = 100 * get_data("countExecutions") / $possible;
        }
        ?>
        
        <div class="delivery-executions-progress">
            <progress max="<?= $possible ?>" value="<?= get_data("countExecutions") ?>" class="pb-de" title="<?= get_data("countExecutions") ?> <?= __('Executions') ?>">
                <div class="progress-bar">
                    <span style="width: <?= $percent?>%"><?= $limit ?></span>
                </div>
            </progress>
            <span style="position: relative; left: 30px; top: -23px" title="<?= get_data("countExecutions") ?> <?= __('Executions') ?>"><?= get_data("countExecutions") ?></span>
            <p style="width: 100%; top: -10px" data-value="<?= $limit ?>">0</p>
        </div>
        
    </div>
    
    <div class="connected-users">
        <b><?= get_data("connectedUsers") ?></b><br>
        <span><?= __('Connected Users') ?></span>
    </div>
</div>

<?php
Template::inc('footer.tpl', 'tao');
?>
