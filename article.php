<?php
require __DIR__.'/includes/bootstrap.php';
$slug=trim($_GET['slug']??'');$a=null;
if($pdo&&$slug){$s=$pdo->prepare("SELECT * FROM articles WHERE slug=? AND status='published' LIMIT 1");$s->execute([$slug]);$a=$s->fetch();if($a){$pdo->prepare('UPDATE articles SET views=views+1 WHERE id=?')->execute([$a['id']]);$a['views']++;}}
if(!$a){http_response_code(404);$a=['title'=>'Article not found','content'=>'This article is unavailable.','excerpt'=>'','category'=>'','author'=>'ASMR Audio Online','published_at'=>null,'views'=>0,'featured_image'=>'','slug'=>'','meta_title'=>'','meta_description'=>'','canonical_url'=>'','faq_json'=>'','noindex'=>1];}
$title=$a['meta_title']?:$a['title'];$description=$a['meta_description']?:$a['excerpt'];$canonical=$a['canonical_url']?:url($a['slug'].'/');$content=(string)$a['content'];$reading=max(1,(int)ceil(str_word_count(strip_tags($content))/200));$toc=[];
$content=preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h\1>/is',function($m)use(&$toc){$label=trim(strip_tags($m[3]));$id=trim(preg_replace('/[^a-z0-9]+/i','-',strtolower($label)),'-')?:'section';$toc[]=['label'=>$label,'id'=>$id];return '<h'.$m[1].$m[2].' id="'.e($id).'">'.$m[3].'</h'.$m[1].'>';},$content)??$content;
$faqs=[];if(!empty($a['faq_json'])){$decoded=json_decode($a['faq_json'],true);if(is_array($decoded))$faqs=$decoded;}$comments=$related=$products=[];$prev=$next=null;
if($pdo&&!empty($a['id'])){try{$s=$pdo->prepare("SELECT name,body FROM comments WHERE article_id=? AND status='approved' ORDER BY created_at DESC");$s->execute([$a['id']]);$comments=$s->fetchAll();$s=$pdo->prepare("SELECT title,slug,excerpt FROM articles WHERE status='published' AND id<>? AND category=? ORDER BY published_at DESC LIMIT 3");$s->execute([$a['id'],$a['category']??'']);$related=$s->fetchAll();$products=$pdo->query("SELECT product_name,slug,short_description,best_for FROM products WHERE status='published' AND featured=1 ORDER BY updated_at DESC LIMIT 3")->fetchAll();$s=$pdo->prepare("SELECT slug,title FROM articles WHERE status='published' AND published_at < ? ORDER BY published_at DESC LIMIT 1");$s->execute([$a['published_at']??date('Y-m-d H:i:s')]);$prev=$s->fetch()?:null;$s=$pdo->prepare("SELECT slug,title FROM articles WHERE status='published' AND published_at > ? ORDER BY published_at ASC LIMIT 1");$s->execute([$a['published_at']??date('Y-m-d H:i:s')]);$next=$s->fetch()?:null;}catch(Throwable $e){}}
if($faqs)$extra_schema=faq_schema($faqs);require __DIR__.'/includes/header.php';
$articleCssVersion=(string)(filemtime(__DIR__.'/assets/css/article.css')?:1);
?><link rel="stylesheet" href="<?=e(url('assets/css/article.css?v='.$articleCssVersion))?>">
<div class="container">
 <div class="article-layout">
  <div class="article-main">
   <p class="muted"><a href="<?=url()?>">Home</a> / <?=e($a['category']?:'Article')?></p>
   <article><h1><?=e($a['title'])?></h1>
    <?php if($a['featured_image']):?><img loading="lazy" src="<?=e(media_url($a['featured_image']))?>" alt="<?=e($a['title'])?>"><?php endif;?>
    <p class="muted">By <?=e($a['author'])?> &middot; <?=e($a['published_at']?date('F j, Y',strtotime($a['published_at'])):'')?> &middot; <?=$reading?> min read &middot; <?=e((string)$a['views'])?> views</p>
    <?php if($toc):?><aside class="card"><strong>In this guide</strong><ul><?php foreach($toc as $item):?><li><a href="#<?=e($item['id'])?>"><?=e($item['label'])?></a></li><?php endforeach;?></ul></aside><?php endif;?>
    <p><?=e($a['excerpt'])?></p><div class="card"><?=safe_html($content)?></div><?=render_ad('article-inline')?>
    <p class="muted">Wellness information is educational and is not a substitute for professional medical diagnosis or treatment.</p>
    <p><strong>Share:</strong> <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?=rawurlencode($canonical)?>">Facebook</a> &middot; <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?=rawurlencode($canonical)?>&text=<?=rawurlencode($a['title'])?>">X</a></p>
   </article>
   <?php if($faqs):?><section><h2>Frequently asked questions</h2><?php foreach($faqs as $faq):if(empty($faq['question'])||empty($faq['answer']))continue;?><details class="card"><summary><?=e($faq['question'])?></summary><p><?=e($faq['answer'])?></p></details><?php endforeach;?></section><?php endif;?>
   <?php if($products):?><section><h2>Recommended equipment</h2><div class="grid"><?php foreach($products as $p):?><article class="card"><h3><?=e($p['product_name'])?></h3><p><?=e($p['short_description'])?></p><a class="btn" target="_blank" rel="nofollow sponsored noopener" href="<?=url('go/'.$p['slug'].'/')?>">View product</a></article><?php endforeach;?></div><p class="muted">ASMR Audio Online may earn a commission when you purchase through links on this page, at no additional cost to you.</p></section><?php endif;?>
   <?php if($prev||$next):?><nav class="card"><?php if($prev):?><a href="<?=url($prev['slug'].'/')?>">Previous: <?=e($prev['title'])?></a><?php endif;?> <?php if($next):?><a href="<?=url($next['slug'].'/')?>">Next: <?=e($next['title'])?></a><?php endif;?></nav><?php endif;?>
   <?php if($related):?><section><h2>Related articles</h2><div class="grid"><?php foreach($related as $r):?><a class="card" href="<?=url($r['slug'].'/')?>"><h3><?=e($r['title'])?></h3><p><?=e($r['excerpt'])?></p></a><?php endforeach;?></div></section><?php endif;?>
   <section><h2>Comments</h2><?php foreach($comments as $comment):?><div class="card"><p><?=nl2br(e($comment['body']))?></p><small>By <?=e($comment['name'])?></small></div><?php endforeach;?><a class="btn" href="<?=url('comment.php?slug='.rawurlencode($a['slug']))?>">Leave a comment</a></section>
  </div>
  <aside class="article-sidebar" aria-label="Advertisements"><?=render_ad('sidebar-rectangle')?><?=render_ad('sidebar-skyscraper')?></aside>
 </div>
</div>
<?php require __DIR__.'/includes/footer.php';