<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreMessageRequest;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\MessageHistoryRequest;
use App\Http\Requests\GuestsRequest;

class MessageController extends BaseController
{
    public function __construct(public MessageService $messageService) {}

    // Сохранить сообщение
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['sender_id'] = Auth::id();
        $message = $this->messageService->store($data);
        return $this->sendResponse($message, 'Message saved');
    }

    // Получить историю сообщений
    public function history(MessageHistoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $senderId = Auth::id();
        $recipientId = $data['recipient_id'];
        $addressId = $data['address_id'];
        $messages = $this->messageService->history($senderId, $recipientId, $addressId);
        return $this->sendResponse($messages, 'Message history');
    }

    public function chats(Request $request)
    {
        $ownerId = Auth::id();
        $chats = $this->messageService->chatsForOwner($ownerId);
        return response()->json(['data' => $chats]);
    }

    public function chatDetails($id)
    {
        $ownerId = Auth::id();

        // Check if the chat exists between the logged in user and the requested user
        $chat = \App\Models\Message::where(function($q) use ($id, $ownerId) {
                $q->where('sender_id', $id)->where('recipient_id', $ownerId);
            })
            ->orWhere(function($q) use ($id, $ownerId) {
                $q->where('sender_id', $ownerId)->where('recipient_id', $id);
            })
            ->orderByDesc('created_at')
            ->first();

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        // Verify that the current user is either the sender or recipient
        if ($ownerId != $chat->sender_id && $ownerId != $chat->recipient_id) {
            return $this->sendError('You are not authorized to view this chat', 403);
        }

        $customer = \App\Models\User::find($id);
        $address = \App\Models\Address::find($chat->address_id);

        // Get all messages between these users for this address
        $messages = $this->messageService->history($ownerId, $id, $chat->address_id);

        return response()->json([
            'data' => [
                'id' => $id,
                'customer_name' => $customer ? trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? '')) : 'Unknown',
                'address_name' => $address ? $address->street : 'Unknown',
                'customer_id' => $id,
                'address_id' => $chat->address_id,
                'messages' => $messages
            ]
        ]);
    }
}
