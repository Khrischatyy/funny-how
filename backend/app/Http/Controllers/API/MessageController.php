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

    public function chatDetails($id, Request $request)
    {
        $ownerId = Auth::id();
        $chatPartnerId = $id; // This could be a chat partner ID or a chat ID
        $addressId = $request->input('address_id');

        // First, let's try to treat $id as a chat ID (numeric)
        if (is_numeric($id)) {
            // Approach 1: Check if it's a valid chat ID
            $chat = \App\Models\Message::find($id);

            if ($chat) {
                // We found a message with this ID
                $addressId = $chat->address_id;
                // Determine the chat partner ID (if owner is sender, partner is recipient and vice versa)
                $chatPartnerId = $chat->sender_id == $ownerId ? $chat->recipient_id : $chat->sender_id;
            } else {
                // Treat as a user ID instead
                $chatPartnerId = (int)$id;
            }
        }

        // Now we have a user ID in $chatPartnerId
        $chatPartner = \App\Models\User::find($chatPartnerId);
        if (!$chatPartner) {
            return response()->json(['message' => 'Chat partner not found'], 404);
        }

        if (!$addressId) {
            // Try to find the first chat between these two users
            $chat = \App\Models\Message::where(function($q) use ($ownerId, $chatPartnerId) {
                    $q->where('sender_id', $ownerId)
                      ->where('recipient_id', $chatPartnerId);
                })
                ->orWhere(function($q) use ($ownerId, $chatPartnerId) {
                    $q->where('sender_id', $chatPartnerId)
                      ->where('recipient_id', $ownerId);
                })
                ->orderByDesc('created_at')
                ->first();

            if (!$chat) {
                // Find the first address for the owner to create a new conversation
                $address = \App\Models\Address::where('owner_id', $ownerId)->first();

                if (!$address) {
                    return response()->json(['message' => 'No studio address found for this user'], 404);
                }

                return response()->json([
                    'data' => [
                        'id' => 0, // No chat ID yet
                        'customer_name' => trim(($chatPartner->firstname ?? '') . ' ' . ($chatPartner->lastname ?? '')) ?: 'Unknown',
                        'address_name' => $address->street ?: 'Unknown',
                        'customer_id' => $chatPartnerId,
                        'address_id' => $address->id,
                        'messages' => []
                    ]
                ]);
            }

            $addressId = $chat->address_id;
        }

        // At this point we should have both a chat partner ID and an address ID
        $address = \App\Models\Address::find($addressId);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        // Get all messages between these users for this address
        $messages = $this->messageService->history($ownerId, $chatPartnerId, $addressId);

        // If we have messages, use the first one's ID as the chat ID
        $chatId = count($messages) > 0 ? $messages[0]->id : 0;

        return response()->json([
            'data' => [
                'id' => $chatId,
                'customer_name' => trim(($chatPartner->firstname ?? '') . ' ' . ($chatPartner->lastname ?? '')) ?: 'Unknown',
                'address_name' => $address->street ?: 'Unknown',
                'customer_id' => $chatPartnerId,
                'address_id' => $addressId,
                'messages' => $messages
            ]
        ]);
    }

    /**
     * Get chat details with POST method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChatDetails(Request $request): JsonResponse
    {
        $ownerId = Auth::id();
        $chatId = $request->input('chat_id');

        // Get chat from message service based on chat ID
        $chat = \App\Models\Message::where('id', $chatId)->first();

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        // Determine customer ID (if owner is sender, then customer is recipient and vice versa)
        $customerId = $chat->sender_id == $ownerId ? $chat->recipient_id : $chat->sender_id;

        // Verify that the current user is either the sender or recipient
        if ($ownerId != $chat->sender_id && $ownerId != $chat->recipient_id) {
            return $this->sendError('You are not authorized to view this chat', 403);
        }

        $customer = \App\Models\User::find($customerId);
        $address = \App\Models\Address::find($chat->address_id);

        // Get all messages between these users for this address
        $messages = $this->messageService->history($ownerId, $customerId, $chat->address_id);

        return response()->json([
            'data' => [
                'id' => $chatId,
                'customer_name' => $customer ? trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? '')) : 'Unknown',
                'address_name' => $address ? $address->street : 'Unknown',
                'customer_id' => $customerId,
                'address_id' => $chat->address_id,
                'messages' => $messages
            ]
        ]);
    }
}
