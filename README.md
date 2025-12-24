# Layihəni Quraşdırma
bash# Clone
git clone https://github.com/your-username/call-event-service.git
cd call-event-service

# Dependencies
composer install

# Environment
cp .env.example .env
php artisan key:generate

# .env faylını düzəlt
nano .env

# Migration
php artisan migrate

# Autoload
composer dump-autoload

# Cache təmizlə
php artisan config:clear
php artisan cache:clear

# .env Konfiqurasiyası

envDB_CONNECTION=mysql
DB_DATABASE=call_event_db
DB_USERNAME=root
DB_PASSWORD=your_password

RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
RABBITMQ_QUEUE=call-events

API_TOKEN=your-secure-random-token

# Token Generasiya
bashphp -r "echo bin2hex(random_bytes(32));"

# Serveri İşə Salma

bashphp artisan serve
API: http://localhost:8000 

Base URL
http://localhost:8000/api/v1

Authentication
Authorization: Bearer your-api-token
Endpoints

# Call Event Yaratma

httpPOST /call-events
Request Body:
json{
  "call_id": "CALL-123456",
  "caller_number": "+994501234567",
  "called_number": "+994551234567",
  "event_type": "call_started",
  "timestamp": "2025-12-24T10:30:00Z"
}

Nümunə event Tipləri:

call_started - Zəng başladı
call_ended - Zəng sonlandı (duration tələb olunur)
call_held - Zəng səssizə alındı
call_transferred - Zəng yönləndirildi
call_missed - Buraxılmış zəng

Response (200 OK):
json{
  "status": "queued",
  "message": "Call event uğurla qəbul edildi və queue-a əlavə olundu",
  "data": {
    "log_id": 1,
    "call_id": "CALL-123456"
  }
}


# RabbitMQ İnteqrasiyası

Qoşulma: Laravel RabbitMQ serverinə qoşulur
Queue Yaradılması: call-events adlı durable queue
Mesaj Göndərmə: JSON formatda event data
Persistent Messages: Mesajlar disk-ə yazılır

# Management UI:
http://localhost:15672

Terminaldan yoxlanilma: Get-Service | Where-Object {$_.Name -like "*Rabbit*"}

# Bütün testlər
php artisan test



