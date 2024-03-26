<!DOCTYPE html>
<html>
    <body>
        <?php if ($name ?? null): ?>
            <p>Hello, <?=$name?>!</p>
        <?php else: ?>
            <p>Howdy, Stranger!</p>
        <?php endif; ?>
    </body>
</html>