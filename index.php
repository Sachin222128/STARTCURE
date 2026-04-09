<?php 
// 1. Path Fix: index.php 
include "views/header.php"; 
?>
<style>
    .hero-banner {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                    url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=1350&q=80');
        background-size: cover; 
        background-position: center; 
        height: 500px;
        display: flex; 
        flex-direction: column; 
        justify-content: center;
        align-items: center; 
        color: white; 
        text-align: center; 
        position: relative;
    }
    .hero-banner h1 { font-size: 3.5rem; font-weight: bold; }
    /* Tracking Box Styling */
    .tracking-wrapper { margin-top: -80px; z-index: 10; position: relative; }
    .tracking-container {
        background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        max-width: 900px; margin: 0 auto; overflow: hidden;
    }
    .search-options {
        display: flex; background: #f8f9fa; border-bottom: 1px solid #eee; padding: 0 20px;
    }
    .option-item {
        padding: 12px 20px; cursor: pointer; font-weight: 600; color: #777;
        border-bottom: 3px solid transparent; transition: 0.3s;
    }
    .option-item.active { color: #f17b21; border-bottom-color: #f17b21; }
    
    /* Form Area */
    .track-form-area { padding: 25px; display: flex; align-items: center; }
    .track-input {
        border: none; border-bottom: 2px solid #eee; border-radius: 0;
        font-size: 1.2rem; padding: 10px 0; width: 100%; outline: none;
    }
    .track-input:focus { border-color: #f17b21; box-shadow: none; }
    .btn-track-submit {
        background: #f17b21; color: white; border: none; padding: 12px 30px;
        border-radius: 8px; margin-left: 20px; transition: 0.3s; font-weight: bold;
    }
    /* Features Section */
    .feature-section { padding: 80px 0; background: #f9f9f9; }
    /* AI Chatbot Styles */
    #chat-content::-webkit-scrollbar { width: 5px; }
    #chat-content::-webkit-scrollbar-thumb { background: #0d6efd; border-radius: 10px; }
    .bot-msg { background: #f1f0f0; padding: 8px 12px; border-radius: 15px 15px 15px 0px; margin-bottom: 10px; display: inline-block; max-width: 85%; }
    .user-msg { background: #0d6efd; color: white; padding: 8px 12px; border-radius: 15px 15px 0px 15px; margin-bottom: 10px; float: right; clear: both; max-width: 85%; }
</style>
<div class="hero-banner">
    <div class="container">
        <h1>Delivering reliability at the speed of business</h1>
        <p class="fs-4">Smart logistics solutions designed for a connected world</p>
    </div>
</div>
<div class="container tracking-wrapper">
    <div class="tracking-container">
        <div class="search-options">
            <div class="option-item active" onclick="setSearchType(this, 'AWB No.')">AWB No.</div>
            <div class="option-item" onclick="setSearchType(this, 'Mobile No.')">Mobile No.</div>
            <div class="option-item" onclick="setSearchType(this, 'Order ID')">Order ID</div>
        </div>
        <div class="track-form-area">
            <form action="views/track_result.php" method="GET" class="d-flex w-100 align-items-center">
                <input type="hidden" name="search_type" id="search_type" value="AWB No.">
                <input type="text" name="tid" id="main_search_input" 
                       class="form-control track-input" 
                       placeholder="Enter AWB No." required>
                <button type="submit" class="btn-track-submit shadow-sm">
                    TRACK <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </form>
        </div>
    </div>
</div>
<div class="feature-section text-center">
    <div class="container">
        <h2 class="mb-5 fw-bold">Why choose <span style="color:#f17b21">Startcure</span></h2>
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white shadow-sm rounded">
                    <h3 class="h5 fw-bold">⚡ Fast Delivery</h3>
                    <p class="text-muted">Delivering parcels. Delivering promises.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white shadow-sm rounded">
                    <h3 class="h5 fw-bold">🛡️ Secure handling</h3>
                    <p class="text-muted">Safe.Secure.Delivered.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="p-4 bg-white shadow-sm rounded">
                    <h3 class="h5 fw-bold">📍 Real-time Tracking</h3>
                    <p class="text-muted">Live updates. Reliable delivery.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<button id="chat-toggle" class="btn btn-primary rounded-circle shadow-lg" style="position:fixed; bottom:20px; right:20px; z-index:1000; width:60px; height:60px;">
    <i class="bi bi-chat-dots-fill fs-4"></i>
</button>
<div id="chat-box" class="card shadow-lg d-none" style="position:fixed; bottom:90px; right:20px; width:350px; z-index:1000; border-radius:15px; border:none;">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
        <strong><i class="bi bi-robot me-2"></i>Startcure AI Assistant</strong>
        <button type="button" class="btn-close btn-close-white" onclick="toggleChat()"></button>
    </div>
    <div id="chat-content" class="card-body overflow-auto" style="height:300px; font-size:14px; background: #fff;">
        <div class="bot-msg">Hello! How can I help you? You can ask for your tracking ID.</div>
    </div>
    <div class="card-footer p-2 bg-white" style="border-radius: 0 0 15px 15px;">
        <div class="input-group">
            <input type="text" id="user-msg" class="form-control border-0 bg-light" placeholder="Type message..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()" class="btn btn-primary rounded-pill ms-2 px-3"><i class="bi bi-send"></i></button>
        </div>
    </div>
</div>
<script>
    function setSearchType(element, type) {
        document.querySelectorAll('.option-item').forEach(item => item.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('main_search_input').placeholder = "Enter " + type;
        document.getElementById('search_type').value = type;
    }
    const chatToggle = document.getElementById('chat-toggle');
    const chatBox = document.getElementById('chat-box');

    chatToggle.addEventListener('click', toggleChat);
    function toggleChat() {
        chatBox.classList.toggle('d-none');
    }
    function handleKeyPress(e) {
        if (e.key === 'Enter') sendMessage();
    }
    function sendMessage() {
        const input = document.getElementById('user-msg');
        const msg = input.value.trim();
        if (msg === "") return;
        const chatContent = document.getElementById('chat-content');
        chatContent.innerHTML += `<div class="user-msg">${msg}</div>`;
        input.value = "";
        chatContent.scrollTop = chatContent.scrollHeight;
        fetch('routes/ai_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(msg)
        })
        .then(response => response.json())
        .then(data => {
            chatContent.innerHTML += `<div class="bot-msg">${data.reply}</div>`;
            chatContent.scrollTop = chatContent.scrollHeight;
        })
        .catch(err => {
            chatContent.innerHTML += `<div class="bot-msg text-danger">Unable to connect to the server.</div>`;
        });
    }
</script>
<?php 
include "views/footer.php"; 
?>