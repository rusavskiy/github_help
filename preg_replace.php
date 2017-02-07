<form enctype="application/x-www-form-urlencoded" method="post">
    <label>preg_replace</label>
    <br/><br/>

    <label>
        <textarea name="pattern" placeholder="$pattern"
                  style="width: 100%;font-size: 16px;"><?= !empty($_POST['pattern']) ? $_POST['pattern'] : '' ?></textarea>
    </label>
    <br/>

    <label>
        <textarea name="replacement" placeholder="$replacement"
                  style="width: 100%"><?= !empty($_POST['replacement']) ? $_POST['replacement'] : '' ?></textarea>
    </label>
    <br/>

    <label>
        <textarea name="subject" placeholder="$subject"
                  style="width: 100%; height: 200px"><?= !empty($_POST['subject']) ? $_POST['subject'] : '' ?></textarea>
    </label>
    <br/>

    <button type="submit" style="width: 100%">send</button>
</form>

<?php

if (!empty($_POST)) {
    $count = null;
    $returnValue = preg_replace($_POST['pattern'], $_POST['replacement'], $_POST['subject'], -1, $count);
    echo"<pre>-> count: ";print_r($count);echo"</pre>";
    echo "<pre>-> result: "; print_r(htmlspecialchars($returnValue)); echo "</pre>";
    die;
}