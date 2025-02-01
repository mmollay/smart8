<?php
function renderStatsCards() {
    ?>
    <div class="ui three cards">
        <!-- Balance Card -->
        <div class="ui card">
            <div class="content">
                <div class="ui tiny statistic">
                    <div class="value">
                        <i class="bitcoin icon"></i>
                        <span id="balance">-</span>
                    </div>
                    <div class="label">Balance USDT</div>
                </div>
            </div>
        </div>
        <!-- Available Card -->
        <div class="ui card">
            <div class="content">
                <div class="ui tiny statistic">
                    <div class="value">
                        <i class="dollar icon"></i>
                        <span id="available">-</span>
                    </div>
                    <div class="label">Available USDT</div>
                </div>
            </div>
        </div>
        <!-- PnL Card -->
        <div class="ui card">
            <div class="content">
                <div class="ui tiny statistic">
                    <div class="value">
                        <i class="chart line icon"></i>
                        <span id="pnl">-</span>
                    </div>
                    <div class="label">PnL USDT</div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
