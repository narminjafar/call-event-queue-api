# Layihəni Quraşdırma

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer dump-autoload
 ```
# .env Konfiqurasiyası

```DB_CONNECTION=mysql
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
```
# Token Generasiya
```bashphp -r "echo bin2hex(random_bytes(32));"```

# Serveri İşə Salma

```php artisan serve```
BASE URL:
```http://localhost:8000/api ```
Authentication :
```Authorization: Bearer your-api-token ```

# Call Event Yaratma

```POST /call-events
Request Body:
json{
  "call_id": "CALL-123456",
  "caller_number": "+994501234567",
  "called_number": "+994551234567",
  "event_type": "call_started",
  "timestamp": "2025-12-24T10:30:00Z"
}
```

Nümunə event Tipləri:

```call_started - Zəng başladı
call_ended - Zəng sonlandı (duration tələb olunur)
call_held - Zəng səssizə alındı
call_transferred - Zəng yönləndirildi
call_missed - Buraxılmış zəng
```
```Response (200 OK):
json{
  "status": "queued",
  "message": "Call event uğurla qəbul edildi və queue-a əlavə olundu",
  "data": {
    "log_id": 1,
    "call_id": "CALL-123456"
  }
}
```

# RabbitMQ İnteqrasiyası

```Qoşulma: Laravel RabbitMQ serverinə qoşulur
Queue Yaradılması: call-events adlı durable queue
Mesaj Göndərmə: JSON formatda event data
Persistent Messages: Mesajlar disk-ə yazılır
```
# Management UI:
```http://localhost:15672```

PowerShell check: ``` Get-Service | Where-Object {$_.Name -like "*Rabbit*"}```

<img width="1500" height="730" alt="screencapture-localhost-15672-2025-12-24-12_52_54" src="https://github.com/user-attachments/assets/f28df77c-0fd1-40b9-9cf0-9e819d47d779" />

# Bütün testlər
```php artisan test```

```

   PASS  Tests\Unit\CallEventServiceTest
  ✓ process event successfully                                                                                                                                                                             0.58s  
  ✓ process event queue fails                                                                                                                                                                              0.01s  

   PASS  Tests\Feature\CallEventApiTest
  ✓ can create call event successfully                                                                                                                                                                     0.12s  
  ✓ duration required for call ended event                                                                                                                                                                 0.02s  
  ✓ endpoint requires authentication                                                                                                                                                                       0.01s  
  ✓ endpoint rejects invalid token                                                                                                                                                                         0.01s  

```

