<div class="sandbox_half">
    <section type="card" class="expansion-only">
        <header type="card_header">Theamus API Sandbox</header>
        <section type="card_expansion">
            <div class="card_text-wrapper">
                <p>This is the Theamus API Sandbox, where you can test out all of your API requests in one spot, without having to refresh the page.</p>
                <p>This sandbox isn't virtual, though. So anything you do here will affect the files/database of your Theamus installation.</p>
                <p><strong>Be careful!</strong></p>
            </div>
        </section>
    </section>
    
    <section type="card">
        <header type="card_header">API Information</header>
        <div class="card_input-wrapper">
            <?php $features = $Sandbox->get_features(); ?>
            <select id="feature"
                    data-label="Feature">
                <?php
                if (!$features) echo "<option>Couldn't get a listing of features, check the query logs.</options>";
                else {
                    foreach ($features as $feature) {
                        echo "<option value='{$feature['alias']}'>{$feature['name']}</option>";
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="card_input-wrapper">
            <select id="function"
                    data-label="Function"></select>
        </div>
        
        <hr class="card_section-split">
        
        <div class="card_input-wrapper">
            <select id="requestType"
                    data-label="Request Type">
                <option value="post">POST</option>
                <option value="get">GET</option>
            </select>
        </div>
    </section>
    
    <section type="card" id="dataCard">
        <section type="card_corner-extension" class="right">
            <button type="button" class="default" id="addData">Add Data</button>
            <button type="button" class="success" id="send">Send</button>
        </section>
        
        <header type="card_header">Send Data</header>
        
        <div id="dataInformation"></div>
        
        <div id="dataDefault" class="card_text-wrapper">
            There's no data here right now!
        </div>
    </section>
</div>

<div class="sandbox_half">
    <section type="card" id="sendinfo">
        <header type="card_header">Send Information</header>
        
        <pre id="info" class="prettyprint">No information has been sent yet!</pre>
    </section>
    
    <section type="card">
        <header type="card_header">Return Data</header>
        
        <pre id="data" class="prettyprint">{}</pre>
    </section>
    
    <section type="card">
        <header type="card_header">Return Errors</header>
        
        <pre id="errors" class="prettyprint">{}</pre>
    </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    sandbox = new Sandbox();
    sandbox.loadFeatureFunctions().listenFeatureFunctions().listenSend();
});
</script>