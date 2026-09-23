<?php
/**
 * Title: Archive Query Loop
 * Slug: xstore/template-query-loop
 * Categories: query
 * Block Types: core/query
 */
?>

<!-- wp:query {"query":{"perPage":10,"postType":"post","order":"desc","orderBy":"date","inherit":true}} -->
<div class="wp-block-query">
    <!-- wp:post-template -->
    <!-- wp:post-title {"isLink":true} /-->
    <!-- wp:post-excerpt /-->
    <!-- /wp:post-template -->

    <!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"space-between"}} -->
    <!-- wp:query-pagination-previous /-->
    <!-- wp:query-pagination-numbers /-->
    <!-- wp:query-pagination-next /-->
    <!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
