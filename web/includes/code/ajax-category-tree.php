<?php

include '../../conf/loadconfig.inc.php';

$modalCategoryModule = $_GET['module'];
$listingLevelObj = new ListingLevel();

include '../modals/modal-categoryselect.php';

?>

<script src="/scripts/categorytree.js"></script>

<script>
    loadCategoryTree('all', '<?= $modalCategoryModule ?>_', '<?= ucwords($modalCategoryModule) ?>Category', 0, 0, '<?=DEFAULT_URL?>',<?=SELECTED_DOMAIN_ID?>);
</script>