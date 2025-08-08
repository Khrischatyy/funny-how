<template>
  <div>
    <NuxtLayout
      :title="chat?.customer_name || 'Chat'"
      class="text-white flex flex-col min-h-screen"
      name="dashboard"
    >
      <div class="container mx-auto px-2 md:px-4 h-full flex flex-col">
        <!-- Header -->
        <div class="py-4 border-b border-gray-700 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <button @click="router.back()" class="text-gray-400 hover:text-white">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
              <span class="text-white font-semibold">{{ getInitials(chat?.customer_name || '') }}</span>
            </div>
            <div>
              <h2 class="font-semibold text-white">{{ chat?.customer_name }}</h2>
              <p class="text-sm text-gray-400">{{ chat?.address_name }}</p>
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div
          ref="messagesContainer"
          class="flex-1 overflow-y-auto py-4 space-y-4"
          @scroll="handleScroll"
        >
          <div v-if="isLoading" class="flex justify-center items-center py-8">
            <div class="spinner"></div>
          </div>

          <div v-else-if="error" class="text-red-500 text-center py-8">
            {{ error }}
          </div>

          <template v-else>
            <div v-for="message in messages" :key="message.id"
                 :class="[
                   'flex',
                   message.sender_id === currentUserId ? 'justify-end' : 'justify-start'
                 ]"
            >
              <div
                :class="[
                  'max-w-[70%] rounded-lg p-3',
                  message.sender_id === currentUserId
                    ? 'bg-blue-500 text-white'
                    : 'bg-gray-700 text-white'
                ]"
              >
                <p class="text-sm">{{ message.content }}</p>
                <p class="text-xs mt-1 opacity-70">
                  {{ formatTime(message.created_at) }}
                </p>
              </div>
            </div>
          </template>
        </div>

        <!-- Input -->
        <div class="border-t border-gray-700 p-4">
          <form @submit.prevent="sendMessage" class="flex space-x-4">
            <input
              v-model="newMessage"
              type="text"
              placeholder="Type a message..."
              class="flex-1 bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              :disabled="isSending"
            />
            <button
              type="submit"
              class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50"
              :disabled="!newMessage.trim() || isSending"
            >
              <span v-if="!isSending">Send</span>
              <div v-else class="spinner-small"></div>
            </button>
          </form>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<style scoped>
.spinner {
  border: 4px solid rgba(255, 255, 255, 0.2);
  border-left-color: #ffffff;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

.spinner-small {
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-left-color: #ffffff;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '~/src/lib/api'
import { useSessionStore } from '~/src/entities/Session'
import { io } from 'socket.io-client'

type Message = {
  id: number
  content: string
  sender_id: number
  recipient_id: number
  address_id: number
  is_read: boolean
  created_at: string
  updated_at: string
}

type ChatResponse = {
  id: string
  customer_name: string
  address_name: string
  customer_id: string
  address_id: number
  messages: Message[]
}

type Chat = {
  id: number
  customer_name: string
  address_name: string
  customer_id: number
  address_id: number
}

const route = useRoute()
const router = useRouter()
const chatId = computed(() => route.params.id as string)
const messagesContainer = ref<HTMLElement | null>(null)
const messages = ref<Message[]>([])
const chat = ref<Chat | null>(null)
const newMessage = ref('')
const isLoading = ref(true)
const isSending = ref(false)
const error = ref('')
const page = ref(1)
const hasMore = ref(true)
const session = computed(() => useSessionStore())
const currentUserId = computed(() => session.value.user?.id)
const socket = ref<any>(null)
const isConnected = ref(false)

const connectSocket = () => {
  try {
    console.log('[chat] Connecting to WebSocket...')
    const host = window.location.hostname === 'localhost' ? 'http://localhost:6001' : 'https://funny-how.com'
    console.log('[chat] WebSocket host:', host)
    const token = session.value.accessToken
    console.log('[chat] Using token:', token)

    socket.value = io(host, {
      path: '/socket.io/',
      auth: {
        token: token
      },
      transports: ['websocket', 'polling']
    })

    socket.value.on('connect', () => {
      console.log('[chat] Connected to WebSocket')
      isConnected.value = true
      // Load message history when connected
      if (chat.value) {
        console.log('[chat][debug] emit get-message-history', {
          addressId: chat.value.address_id,
          recipientId: chat.value.customer_id
        })
        socket.value.emit('get-message-history', {
          addressId: chat.value.address_id,
          recipientId: chat.value.customer_id
        })
      }
    })

    socket.value.on('connect_error', (error: any) => {
      console.error('[chat] Connection error:', error)
      isConnected.value = false
    })

    socket.value.on('error', (error: any) => {
      console.error('[chat] Socket error:', error)
    })

    socket.value.on('disconnect', (reason: string) => {
      console.log('[chat] Disconnected:', reason)
      isConnected.value = false
    })

    socket.value.on('new-message', (message: Message) => {
      console.log('[chat][debug] new-message received:', message)
      messages.value.push(message)
      nextTick(() => scrollToBottom())
    })

    socket.value.on('message-history', (history: Message[]) => {
      console.log('[chat][debug] message-history received:', history)
      messages.value = history
      nextTick(() => scrollToBottom())
    })

  } catch (error) {
    console.error('[chat] Error connecting to WebSocket:', error)
  }
}

onMounted(() => {
  fetchChatDetails()
  connectSocket()
})

onUnmounted(() => {
  if (socket.value) {
    socket.value.disconnect()
  }
})

const fetchChatDetails = async () => {
  isLoading.value = true
  error.value = ''

  try {
    // Using POST request to get chat details with the user ID
    const { post } = useApi({
      url: `/messages/chats/${chatId.value}`,
      auth: true,
      body: {}
    })

    const response = await post() as { data: ChatResponse }
    const chatData = response.data

    chat.value = {
      id: Number(chatData.id),
      customer_name: chatData.customer_name,
      address_name: chatData.address_name,
      customer_id: Number(chatData.customer_id),
      address_id: chatData.address_id
    }

    if (chatData.messages && Array.isArray(chatData.messages)) {
      messages.value = chatData.messages
      nextTick(() => scrollToBottom())
    }
  } catch (err) {
    console.error('Error fetching chat details:', err)
    error.value = 'Failed to load chat details'
  } finally {
    isLoading.value = false
  }
}

const fetchMessages = async (isInitial = false) => {
  if (isLoading.value) return

  if (isInitial) {
    page.value = 1
    hasMore.value = true
  }

  isLoading.value = true

  try {
    // If using pagination in the future, you can implement it here
    // Currently, all messages are returned from the chatDetails endpoint
  } catch (err) {
    console.error('Error fetching messages:', err)
    error.value = 'Failed to load messages'
  } finally {
    isLoading.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || isSending.value || !chat.value) return

  isSending.value = true

  try {
    // First try to send via API directly
    const { post } = useApi({
      url: '/messages',
      auth: true
    })

    const messageData = {
      recipient_id: parseInt(String(chat.value.customer_id), 10),
      content: newMessage.value.trim(),
      address_id: parseInt(String(chat.value.address_id), 10)
    }

    console.log('Sending message data:', messageData)
    await post(messageData)

    // Also try via socket if connected
    if (isConnected.value && socket.value) {
      console.log('[chat][debug] emit private-message', {
        recipientId: chat.value.customer_id,
        message: newMessage.value,
        addressId: chat.value.address_id
      })

      socket.value.emit('private-message', {
        recipientId: chat.value.customer_id,
        message: newMessage.value,
        addressId: chat.value.address_id
      })
    }

    // Add the message to the local messages list immediately
    const newMsg: Message = {
      id: Date.now(), // Temporary ID
      content: newMessage.value,
      sender_id: Number(currentUserId.value),
      recipient_id: chat.value.customer_id,
      address_id: chat.value.address_id,
      is_read: false,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    }

    messages.value.push(newMsg)
    newMessage.value = ''
    nextTick(() => scrollToBottom())
  } catch (err) {
    console.error('Error sending message:', err)
    error.value = 'Failed to send message'
  } finally {
    isSending.value = false
  }
}

const handleScroll = () => {
  if (!messagesContainer.value) return

  const { scrollTop } = messagesContainer.value
  if (scrollTop === 0 && hasMore.value) {
    fetchMessages(false)
  }
}

const scrollToBottom = () => {
  if (!messagesContainer.value) return
  messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
}

const formatTime = (time: string) => {
  if (!time) return ''
  const date = new Date(time)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}
</script>
