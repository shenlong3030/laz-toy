<?php

$appKey = '104805';
$appSecret = 'PZPEQzns8OeNy0R9OImqowLWIs0zXHAn';
$appLink = 'https://toy1.phukiensh.com/lazop';

// Authentication Link
$authLink = 'https://auth.lazada.com/oauth/authorize?response_type=code&force_auth=true&redirect_uri='.$appLink.'&client_id='.$appKey;
$authUrl = 'https://auth.lazada.com/rest';
$apiUrl = 'https://api.lazada.vn/rest';
?>