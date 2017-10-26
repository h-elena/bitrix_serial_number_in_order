# bitrix_serial_number_in_order
Решите задачу управления, хранения и выдачи серийных номеров компьютерных игр при заказе продукта.

Серийные номера для простоты получаются в формате XLS, то есть менеджер заходит в админку и загружает XLS-файл, в котором находятся серийные номера. Эти серийные номера отдаются пользователям, когда совершается покупка игры.

Пользователь приходит на сайт, оформляет заказ с компьютерными играми, оплачивает и получает серийный номер.

При установке модуля создается событие на полную оплату модуля. Далее при установке создается само событие и почтовый шаблон. При оплате заказа это событие автоматом запускается.

P.S. Для корректной работы модуля необходимо на папку с модулем на сервере поставить mbstring.func_overload = 0
P.P.S. Модуль сделан для современных стандартов, а это означает кодировку - UTF-8