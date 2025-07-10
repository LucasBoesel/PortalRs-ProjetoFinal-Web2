<?php
$apiKey = "d14b58486201c8fc597d9b0792ecd069";

// Cidade padrão ou da URL
$cidade = isset($_GET['cidade']) && !empty(trim($_GET['cidade'])) ? trim($_GET['cidade']) : "Porto Alegre";

// Monta a URL da API
$apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cidade) . "&appid=$apiKey&units=metric&lang=pt_br";

// Faz a requisição com cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Verifica a resposta
if ($response !== false) {
    $data = json_decode($response, true);

    if (isset($data['cod']) && (int)$data['cod'] === 200) {
        $temperatura = round($data['main']['temp']);
        $descricao = ucfirst($data['weather'][0]['description']);
        $icone = $data['weather'][0]['icon'];
        $nomeCidade = $data['name'];
        $umidade = $data['main']['humidity'];
        $vento = $data['wind']['speed'];

        echo "
        <div class='weather-box text-center'>
            <h5>Tempo em {$nomeCidade}</h5>
            <img src='https://openweathermap.org/img/wn/10d@2x.png' alt='Ícone do tempo'>
            <p><strong>{$temperatura}°C</strong> — {$descricao}</p>
            <p>💧 Umidade: {$umidade}%</p>
            <p>🌬️ Vento: {$vento} m/s</p>
        </div>
        ";
    } else {
        echo "<p>❌ Cidade não encontrada ou erro na API: " . htmlspecialchars($data['message']) . "</p>";
    }
} else {
    echo "<p>❌ Erro ao conectar à API do tempo.</p>";
}
?>
