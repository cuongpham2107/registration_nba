#!/bin/bash
# Script quản lý Laravel Reverb với PM2

case "$1" in
    start)
        echo "🚀 Khởi động Laravel Reverb..."
        pm2 start /var/www/html/product/registration_nba/pm2.json
        ;;
    stop)
        echo "🛑 Dừng Laravel Reverb..."
        pm2 stop laravel-reverb
        ;;
    restart)
        echo "🔄 Khởi động lại Laravel Reverb..."
        pm2 restart laravel-reverb
        ;;
    status)
        echo "📊 Trạng thái Laravel Reverb:"
        pm2 info laravel-reverb
        ;;
    logs)
        echo "📝 Xem logs Laravel Reverb:"
        pm2 logs laravel-reverb
        ;;
    monitor)
        echo "📈 Monitor Laravel Reverb:"
        pm2 monit
        ;;
    *)
        echo "Cách sử dụng: $0 {start|stop|restart|status|logs|monitor}"
        exit 1
        ;;
esac

exit 0
