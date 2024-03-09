# WB Token Updater

WB Token Updater - это интерфейс обновления различных токена **WBTokenV3** и **wbx-validation-key**, которые необходимы для работы с внутренним API на портале seller.wildberries.ru. Для этого нужны данные из cookie **wbx-refresh** и **wbx-seller-device-id**.

Получить **wbx-refresh** можно на портале через инструменты разработчика браузера. Либо с помощью расширения: https://github.com/wbstat/WBRefreshToken


# Пример работы

Интерфейс для работы поднят на https://token.wbstat.ru/



# API

Можно работать как в интерфейсе, так и с помощью API.

Пример запроса:
 
    "POST",
    'https://token.wbstat.ru/v1/get_token',
    [
        'body' => json_encode(
            [
                'device_id' => $device_id,
                'refresh_token' => $refresh_token
            ]
        )
    ]
