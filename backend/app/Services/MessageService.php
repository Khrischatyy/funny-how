<?php

namespace App\Services;

use App\Models\Message;

class MessageService
{
    public function store(array $data): Message
    {
        return Message::create($data);
    }

    public function history($senderId, $recipientId, $addressId)
    {
        // Получаем все сообщения между двумя пользователями по адресу, в обе стороны
        return Message::where(function($query) use ($senderId, $recipientId) {
                $query->where('sender_id', $senderId)
                      ->where('recipient_id', $recipientId);
            })
            ->orWhere(function($query) use ($senderId, $recipientId) {
                $query->where('sender_id', $recipientId)
                      ->where('recipient_id', $senderId);
            })
            ->where('address_id', $addressId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function chatsForOwner($ownerId)
    {
        $chats = Message::query()
            ->selectRaw('MIN(id) as id, address_id, CASE WHEN sender_id = ? THEN recipient_id ELSE sender_id END as customer_id, MAX(created_at) as last_message_time', [$ownerId])
            ->where(function($q) use ($ownerId) {
                $q->where('recipient_id', $ownerId)
                  ->orWhere('sender_id', $ownerId);
            })
            ->groupBy('address_id', 'customer_id')
            ->orderByDesc('last_message_time')
            ->get();

        return $chats->map(function($chat) use ($ownerId) {
            $customer = \App\Models\User::find($chat->customer_id);
            $customerName = trim(($customer?->firstname ?? '') . ' ' . ($customer?->lastname ?? ''));
            // debug: log customer_id and customerName
            // \Log::info('customer_id: ' . $chat->customer_id . ', customerName: ' . $customerName);
            $lastMessage = Message::where('address_id', $chat->address_id)
                ->where(function($q) use ($chat) {
                    $q->where('sender_id', $chat->customer_id)
                      ->orWhere('recipient_id', $chat->customer_id);
                })
                ->orderByDesc('created_at')
                ->first();

            return [
                'id' => $chat->id,
                'customer_id' => $chat->customer_id,
                'customer_name' => $customerName ?: 'Unknown',
                'address_id' => $chat->address_id,
                'last_message' => $lastMessage?->content ?? '',
                'last_message_time' => $lastMessage?->created_at ?? $chat->last_message_time,
            ];
        });
    }
}
