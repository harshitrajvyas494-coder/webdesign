<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$modules = [
 'clients' => ['label'=>'Clients','icon'=>'👥','singular'=>'Client','fields'=>[
  'name'=>['Name','text',true], 'phone'=>['Phone','tel',true], 'email'=>['Email','email',false],
  'address'=>['Address','text',false]
 ]],
 'events' => ['label'=>'Events','icon'=>'🎉','singular'=>'Event','fields'=>[
  'title'=>['Event Name','text',true], 'type'=>['Event Type','select',true,['Wedding','Engagement','Birthday','Corporate','Anniversary','Other']],
  'event_date'=>['Event Date','date',true], 'venue'=>['Venue','text',true], 'budget'=>['Budget (₹)','number',false],
  'status'=>['Status','select',true,['Planned','Confirmed','Completed','Cancelled']]
 ]],
 'venues' => ['label'=>'Venues','icon'=>'🏛️','singular'=>'Venue','fields'=>[
  'name'=>['Venue Name','text',true], 'location'=>['Location','text',true], 'capacity'=>['Capacity','number',false],
  'price'=>['Price (₹)','number',false], 'contact'=>['Contact','tel',false]
 ]],
 'vendors' => ['label'=>'Vendors','icon'=>'🤝','singular'=>'Vendor','fields'=>[
  'name'=>['Vendor Name','text',true], 'service'=>['Service','select',true,['Catering','Decoration','Photography','Music & DJ','Florist','Transport','Other']],
  'phone'=>['Phone','tel',true], 'amount'=>['Charges (₹)','number',false], 'status'=>['Status','select',true,['Active','Inactive']]
 ]],
 'bookings' => ['label'=>'Bookings','icon'=>'📋','singular'=>'Booking','fields'=>[
  'client'=>['Client Name','text',true], 'event'=>['Event Name','text',true], 'booking_date'=>['Booking Date','date',true],
  'amount'=>['Booking Amount (₹)','number',true], 'status'=>['Status','select',true,['Pending','Confirmed','Completed','Cancelled']]
 ]],
 'payments' => ['label'=>'Payments','icon'=>'💳','singular'=>'Payment','fields'=>[
  'client'=>['Client Name','text',true], 'purpose'=>['Payment For','text',true], 'amount'=>['Amount (₹)','number',true],
  'payment_date'=>['Payment Date','date',true], 'method'=>['Method','select',true,['Cash','UPI','Card','Bank Transfer']],
  'status'=>['Status','select',true,['Paid','Pending']]
 ]]
];

function file_for($key){ return __DIR__ . '/data/' . preg_replace('/[^a-z]/','',$key) . '.json'; }
function load_rows($key){
 $f=file_for($key); if(!file_exists($f)) return [];
 $d=json_decode(file_get_contents($f),true); return is_array($d)?$d:[];
}
function save_rows($key,$rows){
 $f=file_for($key); file_put_contents($f,json_encode(array_values($rows),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX);
}
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
if (!isset($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16));
$module=$_GET['module']??'dashboard';
if (!isset($modules[$module]) && $module!=='dashboard') $module='dashboard';
$action=$_POST['action']??'';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (!hash_equals($_SESSION['csrf'],$_POST['csrf']??'')){http_response_code(403);die('Invalid request token. Go back and retry.');}
 $key=$_POST['module']??'clients';
 if(isset($modules[$key])){
  $rows=load_rows($key); $id=(int)($_POST['id']??0);
  if($action==='delete'){
   $rows=array_values(array_filter($rows,fn($r)=>(int)$r['id']!==$id));
   save_rows($key,$rows); $_SESSION['flash']='Record deleted successfully.';
  } elseif($action==='save'){
   $record=['id'=>$id?:((int)(microtime(true)*1000))];
   foreach($modules[$key]['fields'] as $field=>$meta){
    $val=trim($_POST[$field]??'');
    if($meta[2] && $val===''){$_SESSION['flash']='Please fill all required fields.'; header("Location: index.php?module=$key");exit;}
    $record[$field]=$val;
   }
   if($id){foreach($rows as &$r){if((int)$r['id']===$id){$r=$record;break;}}unset($r);$_SESSION['flash']='Record updated successfully.';}
   else {$rows[]=$record;$_SESSION['flash']='New record added successfully.';}
   save_rows($key,$rows);
  }
 }
 header("Location: index.php?module=".urlencode($key)); exit;
}
$rows=($module!=='dashboard')?load_rows($module):[];
$q=trim($_GET['q']??'');
if($q!=='') $rows=array_values(array_filter($rows,function($r)use($q){return stripos(implode(' ',array_map('strval',$r)),$q)!==false;}));
$edit=null;
if($module!=='dashboard' && isset($_GET['edit'])) foreach(load_rows($module) as $r) if((int)$r['id']===(int)$_GET['edit']){$edit=$r;break;}
$counts=[]; foreach($modules as $k=>$m)$counts[$k]=count(load_rows($k));
$allEvents=load_rows('events'); $upcoming=array_values(array_filter($allEvents,fn($e)=>!empty($e['event_date']) && $e['event_date']>=date('Y-m-d') && ($e['status']??'')!=='Cancelled'));
usort($upcoming,fn($a,$b)=>strcmp($a['event_date'],$b['event_date']));
$revenue=array_sum(array_map(fn($p)=>($p['status']??'')==='Paid'?(float)($p['amount']??0):0,load_rows('payments')));
$flash=$_SESSION['flash']??''; unset($_SESSION['flash']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>EventAura ERP</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div class="app">
<aside class="sidebar"><div class="brand"><div class="brand-mark">E</div><div><b>EventAura</b><small>MANAGEMENT ERP</small></div></div>
<div class="nav-label">WORKSPACE</div><a class="nav <?= $module==='dashboard'?'active':'' ?>" href="index.php"><span>▦</span> Dashboard</a>
<div class="nav-label">MANAGEMENT</div>
<?php foreach($modules as $k=>$m):?><a class="nav <?= $module===$k?'active':'' ?>" href="index.php?module=<?=$k?>"><span><?=$m['icon']?></span><?=$m['label']?><em><?=$counts[$k]?></em></a><?php endforeach;?>
<div class="sidebar-bottom"><div class="avatar">EV</div><div><b>Administrator</b><small>Local workspace</small></div></div></aside>
<main class="main"><header class="topbar"><div><div class="eyebrow">EVENT & WEDDING MANAGEMENT</div><h1><?= $module==='dashboard'?'Dashboard':h($modules[$module]['label']) ?></h1></div><div class="top-right"><span class="server-dot"></span> Local server <span class="date"><?=date('d M Y')?></span></div></header>
<?php if($flash):?><div class="toast"><?=h($flash)?></div><?php endif;?>
<?php if($module==='dashboard'):?>
<section class="welcome"><div><span class="pill">OVERVIEW</span><h2>Make every event<br><i>unforgettable.</i></h2><p>Your event business, beautifully organised in one place.</p><a class="primary-btn" href="index.php?module=events">＋ Manage Events</a></div><div class="welcome-art"><div class="ring">✦</div><div class="art-card"><span>✧</span><b>Plan. Celebrate.<br>Remember.</b><small>YOUR EVENTS, IN HARMONY</small></div></div></section>
<div class="stats">
<div class="stat"><div class="stat-top"><span>Total Clients</span><b>👥</b></div><strong><?=$counts['clients']?></strong><small>Registered clients</small></div>
<div class="stat"><div class="stat-top"><span>Total Events</span><b>🎉</b></div><strong><?=$counts['events']?></strong><small>All event records</small></div>
<div class="stat"><div class="stat-top"><span>Bookings</span><b>📋</b></div><strong><?=$counts['bookings']?></strong><small>Booking records</small></div>
<div class="stat"><div class="stat-top"><span>Payments Received</span><b>💰</b></div><strong>₹<?=number_format($revenue)?></strong><small>Paid payments only</small></div></div>
<div class="section-head"><div><div class="eyebrow">STAY ON TOP OF IT</div><h3>Upcoming events</h3></div><a class="text-link" href="index.php?module=events">View all →</a></div>
<div class="table-wrap"><table><thead><tr><th>EVENT</th><th>DATE</th><th>VENUE</th><th>TYPE</th><th>STATUS</th></tr></thead><tbody>
<?php foreach(array_slice($upcoming,0,6) as $e):?><tr><td><b><?=h($e['title']??'')?></b></td><td><?=h($e['event_date']??'')?></td><td><?=h($e['venue']??'')?></td><td><?=h($e['type']??'')?></td><td><span class="status"><?=h($e['status']??'')?></span></td></tr><?php endforeach;?>
<?php if(!$upcoming):?><tr><td colspan="5" class="empty">No upcoming events yet. Add your first event to get started.</td></tr><?php endif;?></tbody></table></div>
<div class="quick-grid"><?php foreach($modules as $k=>$m):?><a href="index.php?module=<?=$k?>" class="quick"><span><?=$m['icon']?></span><b>Manage <?=$m['label']?></b><small>Open module →</small></a><?php endforeach;?></div>
<?php else: $m=$modules[$module];?>
<div class="content-grid"><section class="panel form-panel"><div class="panel-title"><div><div class="eyebrow"><?= $edit?'UPDATE RECORD':'CREATE NEW RECORD'?></div><h3><?=$edit?'Edit':'Add'?> <?=$m['singular']?></h3></div><span class="panel-icon"><?=$m['icon']?></span></div>
<form method="post" class="record-form"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="module" value="<?=h($module)?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h($edit['id']??'')?>">
<div class="fields"><?php foreach($m['fields'] as $field=>$meta):?><label><?=$meta[0]?><?=$meta[2]?'<i>*</i>':''?>
<?php if($meta[1]==='select'):?><select name="<?=h($field)?>" <?=$meta[2]?'required':''?>><option value="">Choose <?=$meta[0]?></option><?php foreach($meta[3] as $opt):?><option <?=($edit[$field]??'')===$opt?'selected':''?>><?=h($opt)?></option><?php endforeach;?></select>
<?php else:?><input type="<?=h($meta[1])?>" name="<?=h($field)?>" value="<?=h($edit[$field]??'')?>" <?=$meta[2]?'required':''?> <?= $meta[1]==='number'?'min="0"':''?> placeholder="Enter <?=h(strtolower($meta[0]))?>"><?php endif;?></label><?php endforeach;?></div>
<div class="form-actions"><button class="primary-btn" type="submit"><?=$edit?'Save Changes':'＋ Add '.$m['singular']?></button><?php if($edit):?><a class="cancel-btn" href="index.php?module=<?=$module?>">Cancel</a><?php endif;?></div></form></section>
<section class="panel info-panel"><div class="eyebrow">MODULE GUIDE</div><h3><?=$m['label']?> workspace</h3><p>Keep your <?=$m['label']?> information organised. Add new records, update existing details, or search and remove records whenever required.</p><div class="guide-row"><span>＋</span><div><b>Create</b><small>Add a new <?=$m['singular']?> record.</small></div></div><div class="guide-row"><span>⌕</span><div><b>Search</b><small>Find records by entering a keyword.</small></div></div><div class="guide-row"><span>✎</span><div><b>Update</b><small>Edit saved information anytime.</small></div></div><div class="guide-row"><span>⌫</span><div><b>Delete</b><small>Remove records you no longer need.</small></div></div></section></div>
<div class="section-head list-head"><div><div class="eyebrow">RECORD DIRECTORY</div><h3>All <?=$m['label']?> <span class="count"><?=$counts[$module]?></span></h3></div><form class="search" method="get"><input type="hidden" name="module" value="<?=$module?>"><input name="q" value="<?=h($q)?>" placeholder="Search records..."><button>⌕</button></form></div>
<div class="table-wrap"><table><thead><tr><?php foreach($m['fields'] as $meta):?><th><?=h(strtoupper($meta[0]))?></th><?php endforeach;?><th>ACTIONS</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><?php foreach($m['fields'] as $field=>$meta):?><td><?=h($r[$field]??'—')?></td><?php endforeach;?><td class="actions"><a class="edit" href="index.php?module=<?=$module?>&edit=<?=(int)$r['id']?>">Edit</a><form method="post" onsubmit="return confirm('Delete this record permanently?')"><input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>"><input type="hidden" name="module" value="<?=$module?>"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="action" value="delete"><button class="delete">Delete</button></form></td></tr><?php endforeach;?>
<?php if(!$rows):?><tr><td colspan="<?=count($m['fields'])+1?>" class="empty"><?=$q?'No matching records found.':'No records yet. Add your first record using the form above.'?></td></tr><?php endif;?></tbody></table></div>
<?php endif;?>
<footer>EventAura ERP <span>•</span> Built for local XAMPP · Data stored in local JSON files</footer></main></div><script src="assets/app.js"></script></body></html>