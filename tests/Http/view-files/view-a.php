<?php /** @var \Laucov\WebFwk\Services\LanguageService $lang */ ?>
<?php /** @var \Laucov\Views\Builder $this */ ?>
<!DOCTYPE html>
<html>
    <body class="body--<?=$theme ?? 'light'?>">
        <h1><?=$title?></h1>
        <?php if ($name ?? null): ?>
            <p><?=$lang->findMessage('user-greeting', [$name])?></p>
        <?php else: ?>
            <p><?=$lang->findMessage('stranger-greeting', [])?></p>
        <?php endif; ?>
    </body>
</html>