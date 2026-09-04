<?php
require __DIR__.'/includes/bootstrap.php';
$slug=trim($_GET['slug']??'');$p=null;
if($pdo&&$slug){$s=$pdo->prepare("SELECT * FROM products WHERE slug=? AND status='published' LIMIT 1");$s->execute([$slug]);$p=$s->fetch();}
if(!$p){http_response_code(404);$p=['product_name'=>'Product not found','short_description'=>'This product is unavailable.','slug'=>'','button_text'=>'Back'];}
$title=$p['product_name'];$description=$p['short_description']??'';
if($p['slug'])$schema=product_schema($p,url('products/'.$p['slug'].'/'));
require __DIR__.'/includes/header.php';
?><div class="container"><article class="card"><h1><?=e($p['product_name'])?></h1><p><?=e($description)?></p><?php if(!empty($p['best_for'])):?><p><strong>Best for:</strong> <?=e($p['best_for'])?></p><?php endif;?><a class="btn" href="<?=url('go/'.e($p['slug']).'/')?>" target="_blank" rel="nofollow sponsored noopener"><?=e($p['button_text']??'View product')?></a><p class="muted">Affiliate disclosure: we may earn a commission at no additional cost to you.</p></article></div><?php require __DIR__.'/includes/footer.php';
