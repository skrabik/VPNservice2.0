<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutlineService
{
    private string $api_url;

    public function __construct(Server $server)
    {
        $this->api_url = $server->parameters()->where('key', 'url')->first()->value;
    }

    /**
     * Создает новый ключ доступа
     *
     * @param  string|null  $name  Имя ключа (опционально)
     * @return array|null Данные созданного ключа или null в случае ошибки
     */
    public function createUser(?string $name = null): ?array
    {
        try {
            $response = Http::withoutVerifying()->post($this->api_url.'/access-keys', [
                'method' => 'aes-256-gcm',
                'password' => $name,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Outline API Error: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Удаляет ключ доступа
     *
     * @param  int  $user_id  ID ключа доступа
     * @return bool Успешность операции
     */
    public function deleteUser(int $user_id): bool
    {
        try {
            $response = Http::withoutVerifying()->delete($this->api_url.'/access-keys/'.$user_id);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Получает список всех ключей доступа
     *
     * @return array|null Список ключей или null в случае ошибки
     */
    public function listUsers(): ?array
    {
        try {
            $response = Http::withoutVerifying()->get($this->api_url.'/access-keys');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Outline API Error: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Получает информацию о конкретном ключе доступа
     *
     * @param  int  $user_id  ID ключа доступа
     * @return array|null Данные ключа или null в случае ошибки
     */
    public function getUser(int $user_id): ?array
    {
        try {
            $response = Http::withoutVerifying()->get($this->api_url.'/access-keys/'.$user_id);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Outline API Error: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Устанавливает лимит трафика для ключа доступа
     *
     * @param  int  $user_id  ID ключа доступа
     * @param  int  $bytes  Лимит в байтах
     * @return bool Успешность операции
     */
    public function setDataLimit(int $user_id, int $bytes): bool
    {
        try {
            $response = Http::withoutVerifying()->put($this->api_url.'/access-keys/'.$user_id.'/data-limit', [
                'limit' => ['bytes' => $bytes],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Удаляет лимит трафика для ключа доступа
     *
     * @param  int  $user_id  ID ключа доступа
     * @return bool Успешность операции
     */
    public function removeDataLimit(int $user_id): bool
    {
        try {
            $response = Http::withoutVerifying()->delete($this->api_url.'/access-keys/'.$user_id.'/data-limit');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Получает статистику использования для ключа доступа
     *
     * @param  int  $user_id  ID ключа доступа
     * @return array|null Статистика использования или null в случае ошибки
     */
    public function getDataUsage(int $user_id): ?array
    {
        try {
            $response = Http::withoutVerifying()->get($this->api_url.'/access-keys/'.$user_id.'/data-usage');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Outline API Error: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Переименовывает ключ доступа
     *
     * @param  int  $user_id  ID ключа доступа
     * @param  string  $name  Новое имя
     * @return bool Успешность операции
     */
    public function renameUser(int $user_id, string $name): bool
    {
        try {
            $response = Http::withoutVerifying()->put($this->api_url.'/access-keys/'.$user_id, [
                'name' => $name,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Устанавливает глобальный лимит трафика для всех ключей
     *
     * @param  int  $bytes  Лимит в байтах
     * @return bool Успешность операции
     */
    public function setGlobalDataLimit(int $bytes): bool
    {
        try {
            $response = Http::withoutVerifying()->put($this->api_url.'/server/access-key-data-limit', [
                'limit' => ['bytes' => $bytes],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Удаляет глобальный лимит трафика
     *
     * @return bool Успешность операции
     */
    public function removeGlobalDataLimit(): bool
    {
        try {
            $response = Http::withoutVerifying()->delete($this->api_url.'/server/access-key-data-limit');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Outline API Exception: '.$e->getMessage());

            return false;
        }
    }
}
