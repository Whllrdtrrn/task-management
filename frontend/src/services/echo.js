import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echoInstance = null

export const createEcho = (token) => {
  if (echoInstance) {
    console.log('♻️ Echo already exists, returning existing instance')
    return echoInstance
  }

  const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY
  const websocketsEnabled = import.meta.env.VITE_WEBSOCKETS_ENABLED === 'true'

  console.log('🔍 Creating Echo with:', {
    pusherKey: pusherKey ? 'present' : 'missing',
    websocketsEnabled,
    token: token ? 'present' : 'missing',
    tokenStart: token ? token.substring(0, 10) + '...' : 'none'
  })

  if (!pusherKey || !websocketsEnabled) {
    console.log('❌ WebSockets disabled - missing configuration')
    return null
  }

  try {
    window.Pusher = Pusher

    echoInstance = new Echo({
      broadcaster: 'pusher',
      key: pusherKey,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
      forceTLS: true,
      auth: {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      },
      authEndpoint: 'http://localhost:8000/api/broadcasting/auth',
    })

    console.log('✅ Echo instance created with auth endpoint')
    
    echoInstance.connector.pusher.connection.bind('connected', () => {
      console.log('✅ Pusher connected successfully')
    })

    echoInstance.connector.pusher.connection.bind('error', (error) => {
      console.error('❌ Pusher connection error:', error)
    })

    return echoInstance
  } catch (error) {
    console.error('❌ Failed to create Echo instance:', error)
    return null
  }
}

export const destroyEcho = () => {
  if (echoInstance) {
    try {
      echoInstance.disconnect()
      echoInstance = null
      console.log('🔌 Echo instance destroyed successfully')
    } catch (error) {
      console.error('❌ Error destroying Echo:', error)
    }
  }
}

export const getEcho = () => echoInstance