<?php /** @var array $data */ ?>
<div class="copilot-container">
    <div class="copilot-header">
        <div class="copilot-icon"><i class="fas fa-robot"></i></div>
        <h2 class="text-gradient">SupplyGuard AI Copilot</h2>
        <p>Ask about shipments, disruptions, fleet, and cold-chain risks. Powered by real-time application data.</p>
    </div>

    <!-- Suggested Questions -->
    <div class="suggested-questions" id="suggestedQuestions">
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-exclamation-circle me-2" style="color:var(--danger);"></i>Which shipments need immediate attention?</button>
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-chart-line me-2" style="color:var(--primary-light);"></i>Why is SH-1024 high risk?</button>
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-cloud-showers-heavy me-2" style="color:var(--info);"></i>Which shipments are affected by the Vadodara disruption?</button>
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-route me-2" style="color:var(--success);"></i>Suggest an alternative route for SH-1024</button>
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-truck me-2" style="color:var(--accent);"></i>Which idle vehicles can be redeployed?</button>
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-temperature-low me-2" style="color:var(--warning);"></i>Which cold-chain shipments have temperature excursions?</button>
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-bolt me-2" style="color:var(--risk-high);"></i>What is the biggest current logistics risk?</button>
        <button class="suggested-q" onclick="askQuestion(this.textContent)"><i class="fas fa-list-check me-2" style="color:var(--success);"></i>What should the operations team do first?</button>
    </div>

    <!-- Chat Messages -->
    <div class="chat-messages" id="chatMessages"></div>

    <!-- Chat Input -->
    <div class="chat-input-area">
        <input type="text" id="chatInput" placeholder="Ask SupplyGuard AI anything about your supply chain..." onkeypress="if(event.key==='Enter')sendMessage()">
        <button class="chat-send-btn" onclick="sendMessage()" id="sendBtn"><i class="fas fa-paper-plane"></i></button>
    </div>

    <div style="text-align:center;margin-top:12px;font-size:11px;color:var(--text-muted);">
        <i class="fas fa-info-circle me-1"></i> AI responses are based on current database records. Route recommendations use simulated data for this prototype.
    </div>
</div>
