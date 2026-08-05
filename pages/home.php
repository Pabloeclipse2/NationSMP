<?php
/**
 * home.php — landing page
 */

// Mock live stats (in production, poll this from a status API / cron cache table)
$mockOnlinePlayers = 187;
$mockMaxPlayers = 500;
?>

<section class="hero">
    <span class="hero-eyebrow">⚡ Season 4 is live — new biomes, new economy</span>
    <h1 class="hero-title">Build. Trade. Conquer.<br>Welcome to <?= e(SERVER_NAME) ?>.</h1>
    <p class="hero-sub">
        A community-driven survival server with towny nations, player-run markets,
        and a staff team that actually plays with you. Grab the IP and jump in.
    </p>

    <div class="ip-copy" data-copy-ip="<?= e(SERVER_IP) ?>">
        <span class="ip-text"><?= e(SERVER_IP) ?></span>
        <button class="ip-btn" data-copy-ip="<?= e(SERVER_IP) ?>">📋 Click to Copy</button>
    </div>

    <div class="hero-cta">
        <a href="/index.php?page=forum" class="btn btn-secondary">Browse the Forum</a>
        <a href="/index.php?page=apply" class="btn btn-secondary">Apply for Staff</a>
    </div>

    <div class="status-pill">
        <span class="dot-live"></span>
        <span data-player-count="<?= (int) $mockOnlinePlayers ?>"><?= (int) $mockOnlinePlayers ?></span>
        / <?= (int) $mockMaxPlayers ?> players online
    </div>
</section>

<section style="margin-top:64px;">
    <div class="section-head">
        <div>
            <span class="eyebrow">Why play here</span>
            <h2>Built for a community, not just a server</h2>
        </div>
    </div>

    <div class="grid">
        <div class="card glass glow-emerald">
            <div class="card-icon">🏰</div>
            <h3>Towny Nations</h3>
            <p>Found a town, forge alliances, and wage diplomacy across a persistent player-driven map that's never wiped.</p>
        </div>
        <div class="card glass glow-diamond">
            <div class="card-icon">💎</div>
            <h3>Player Economy</h3>
            <p>A fully player-run market — no pay-to-win shops. What you build and mine is what drives the economy.</p>
        </div>
        <div class="card glass glow-pink">
            <div class="card-icon">🛡️</div>
            <h3>Active Staff Team</h3>
            <p>Real moderators, real response times. Apply yourself through our staff application system below.</p>
        </div>
        <div class="card glass glow-emerald">
            <div class="card-icon">🗺️</div>
            <h3>Custom Terrain</h3>
            <p>Hand-tuned world generation with custom biomes, structures, and secrets scattered across the overworld.</p>
        </div>
        <div class="card glass glow-diamond">
            <div class="card-icon">⚔️</div>
            <h3>Seasonal Events</h3>
            <p>PvP tournaments, build competitions, and community votes that shape what gets added next.</p>
        </div>
        <div class="card glass glow-pink">
            <div class="card-icon">💬</div>
            <h3>Community Forum</h3>
            <p>News, suggestions, and general discussion — all in one place, no Discord scrolling required.</p>
        </div>
    </div>
</section>
