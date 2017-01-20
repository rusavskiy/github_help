<form enctype="application/x-www-form-urlencoded" method="post">
    <label>
        <textarea name="json" placeholder="json"
                  style="width: 100%"><?= !empty($_POST['json']) ? $_POST['json'] : '' ?></textarea>
    </label>
    <br/>

    <label>
        <textarea name="array" placeholder="array"
                  style="width: 100%"><?= !empty($_POST['array']) ? $_POST['array'] : '' ?></textarea>
    </label>
    <br/>

    <button type="submit" style="width: 100%">send</button>
</form>

<?php

if (!empty($_POST['json'])) {
    require_once 'varDumperCasper.php';
    \varDumperCasper::dump(json_decode($_POST['json'], true), '');
}

if (!empty($_POST['array'])) {
    echo json_encode(eval('return ' . $_POST['array'] . ';'));
}