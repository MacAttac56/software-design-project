<?php
session_start();
if (!isset($_SESSION['ai_csrf'])) $_SESSION['ai_csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['ai_csrf'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hail Mary | AI Shopping Assistant</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="HailMarystylesheet.css" rel="stylesheet">
<style>
.chat-log{height:430px;overflow-y:auto;background:#f8f9fa;border:1px solid #ddd;border-radius:10px;padding:18px;white-space:pre-wrap}
.chat-msg{margin:0 0 16px;padding:12px;border-radius:10px;max-width:90%}.user-msg{background:#dbeafe;margin-left:auto}.bot-msg{background:white;border:1px solid #e2e8f0}
</style>
</head>
<body>
<header class="site-banner text-center"><h1>Hail Mary</h1><p>Buy, Sell, and Trade with College Students</p></header>
<nav class="navbar navbar-expand-lg navbar-dark custom-navbar"><div class="container-fluid"><a class="navbar-brand" href="HailMaryhomepage.php">Hail Mary</a><div class="navbar-nav ms-auto"><a class="nav-link" href="HailMaryListings.php">Listings</a><a class="nav-link" href="HailMaryCart.php">My Cart</a><a class="nav-link active" href="HailMaryAI.php">AI Assistant</a></div></div></nav>
<main class="container py-5"><h2>AI Shopping Assistant</h2><p>Ask for product recommendations based on your budget, classes, or interests.</p>
<div id="log" class="chat-log" aria-live="polite"><div class="chat-msg bot-msg">Hi! Tell me what you're looking for, such as “What study supplies are available under $50?”</div></div>
<form id="chat-form" class="d-flex gap-2 mt-3"><input id="message" class="form-control" maxlength="1000" required placeholder="Ask about products..." autocomplete="off"><button id="send" class="btn btn-primary" type="submit">Send</button></form>
<p class="text-muted small mt-2">AI recommendations can be inaccurate. Confirm prices and availability before purchasing. Never enter payment information in chat.</p>
<a class="btn btn-outline-secondary" href="HailMaryListings.php">Browse all listings</a>
</main>
<script>
const csrf = <?= json_encode($csrf) ?>;
const form=document.getElementById('chat-form'), log=document.getElementById('log'), field=document.getElementById('message'), send=document.getElementById('send');
function addMessage(text,cls){const el=document.createElement('div');el.className='chat-msg '+cls;el.textContent=text;log.appendChild(el);log.scrollTop=log.scrollHeight;}
form.addEventListener('submit',async e=>{e.preventDefault();const message=field.value.trim();if(!message)return;field.value='';addMessage(message,'user-msg');send.disabled=true;
try{const response=await fetch('ai_backend.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify({message})});const data=await response.json();addMessage(response.ok?data.reply:(data.error||'Request failed'),'bot-msg');}catch(e){addMessage('Could not contact the AI backend. Check Apache and your network.','bot-msg');}finally{send.disabled=false;field.focus();}});
</script>
</body></html>
