<?php

return [
    "default_user_headimg"=>"https://tg.199ho.com/static/img/default_head.png",//默认头像地址
    "web_url_host"=>"https://g.tg.199ho.com/",
    "share_img_host"=>"https://tg.199ho.com/",
    "share_url_host"=>"https://tg.199ho.com/",

    'auto_login_time' => 3600*24*7,
    'effective_apprentice_need_gold' => 100,
    'read_how_gold_get_invitation_reward' => 50,
    'invitation_apprentice_read_gold' => [
        1 => 1400,
        2 => 1400,
        3 => 1400,
        4 => 200,
        5 => 200,
        6 => 200,
        7 => 200
    ],
    'invitation_disciple_read_gold' => [
        1 => 200,
        2 => 200,
        3 => 200,
        4 => 200,
        5 => 200
    ],
    //收徒活动的奖励的钱数
    'apprentice_activity' => [
        //收徒数量 奖励钱数
        [
            'apprentice_num' => 1,
            'record_money' => 4
        ],
        [
            'apprentice_num' => 2,
            'record_money' => 8
        ],
        [
            'apprentice_num' => 4,
            'record_money' => 18
        ],
        [
            'apprentice_num' => 6,
            'record_money' => 32
        ],
        [
            'apprentice_num' => 8,
            'record_money' => 48
        ],
        [
            'apprentice_num' => 15,
            'record_money' => 98
        ],
        [
            'apprentice_num' => 30,
            'record_money' => 216
        ],
        [
            'apprentice_num' => 60,
            'record_money' => 468
        ],
        [
            'apprentice_num' => 120,
            'record_money' => 968
        ],
        [
            'apprentice_num' => 320,
            'record_money' => 2888
        ],
    ],

    //首次收徒阅读奖励的金币数
    'first_invitation_apprentice_read_record' => [
        1 => 2400,
        2 => 1600,
        3 => 1600,
        4 => 1600,
        5 => 1600,
        6 => 1600,
        7 => 1600
    ],

    //app key
    'request_sign_key'=>'6b5695e8570e4176b84153a870634156',
    //一天内相同MEID登陆最大数量,超过则所有封号
    'meidMax' => 5,
    //一天内 相同IP相同师傅 登陆最大数量，超过则封号
    'ipFatherMax' => 10,
    //单个设备注册用户上限
    'registerMax' => 3,
    'rsa_key' => "tblwkSXBYB+JK6PaitpurjL3FIGVuYhkOy519tiohR/6xFYRvay4Ko/ZgM7Hi8+k
sDcDigGduYb2aE8JHOrRkUtpNZm9HVzj5uNS/Vh8OOCfEVHnlWaj7mz9RdfVDc32
cers5TS5zO7K1jj+gSMKqZF4eYVhvRDkm/JgGejf50peanb/lYsP4htQNLLYmCQw
n+T7b6bQkaBDdPIhjq+r0R31ixSSTyN1Z5OnLW+SltjTopf3AGVWthy+miuqIbSn
go8Z3/Fr43kGD0FcpHUIOfE4GohetTVdYV9gAv30VkU3GbtxxI6y4KAP5E+LY3O8
3ZxnHVrgACZ2nd11g6VU9cKEFy/yPwamBEKMn5o5KFbWQ8aRm1xQ+LHUyPz5sMuD
IJ0TB6l6DqER/RCxRfJy4aq8OZS6nZc/JID7EtHvu7hoVZ+bGCJgbvZkrv/x/Zxf
HIQ2XvjXokEY15kTk+DF8WJN/zlWA88RP9ZpQfZKG0l5Aj0AZd9OEk+ZpKyRo/sJ
9bl6DO5yK0H0711zuTOsDQHcV9tUGGUfw2tXA2mDfAC1SYcL2OvwTuDaq9y2ZdQN
dzSrn34PZb3OZh2+FME1QsOq6m1iH+22JqS9LOs+ihM2xLMDbMR0k8Nircch4tJi
5dRVLdOUuPFb5u27GMb/A0ga+Mgv72AxUXfU+acNHvs4cTInGA1CNFY6pfvlrwEX
8C+/IRd4vxs1t4JXVtyo5vhIolZ0WSA4X8mt9jlZtat2IBlhJWZJ4RVV2Ze+Mxof
daUCIWUvKkK2fmqxXv9royI1HBUOX1yneF5mUqrlsWD0mD2J8CiVnt+N+3Zk616+
muxfODKNp90PkZjRgOMWL04r+NmQ8u6H0+tQPz5cz4PLsYy/uev0okfwZictuMoT
xs7f9iYaJJqQOhfzMvTgbS0XEGcm6akEIAvhFS9aVYovZa/LeY/78V0uCAz3LUcl
7tutjb7kuFJoejnREk0mUqBRxrSt6tOHzL/WyJ5Sv/tRMFbuYkYGJTXmpxBoydF/
JfVP6Ujs0vvIIOOJTLR9pOcChHgyf0zPiIBXHC4ATjwa9eOxhzfdlR/V0BRqlUY+
ME6mtCGjbZ+pobHK50SfTGgvjzsrpX2DRRErgLcA2EBeqhDWR3K5Sn85jdkdib0p
TlA35CBa1ZstMnZMa1gyCfZ30Vt3ifTn1B6Vp6JajNFJWyPxPjkuJ2wyf69SEaNK
vZ2w51TItljJRx4W659clNm9mRSglx4jncSLwtN2gqs9Rr0WS7I6064vn9amYwPY
+q4sa1T7+CtJRNhMufB7YKWbM6eAfuIrRQJP6RHY0rWAMqxszqkTOeFkQx8R3pQk
eN8if0J2qWO1M75O+iHrdnurg7uQ+pDY0rGjDonIfvNF/xbbyHDU9hjzFzchr1UA
0Us3PDjGauN+l0aJ9GzabR1HN0VN4p1gGG5QHGJIUaXWj6ZJt1retIg2/Dx7gK+n
Xb2t/wT0iUSy3KE7vX1posKECUVOiFHCHYnL9N0lmIyok+UwZCAbou/gEdOMOwwB
S8qfz3wWO95svvlFgEQDhNgME7sYkz/KoyiVtHZdImGS5kvONu6iD7gs/G5S+vY5",
    "rsa_key_pub" => "AAAAB3NzaC1yc2EAAAADAQABAAABAQDl6fJUR4A0Gw5lNkl7DGGYwc/91b2FJD8EwY5Mg/NmUp5pUtzp4S2fMKBPoj9Cz9Jr4QOEN3jJNTfFMfzKUgXEgY+jBUhbF7jhsZyuH91dpXMknKfgkbHQGeeE5mOWIKgPL2Pz0QDo8K9Ajz1IohpAtRjBeKXAhCp9l2I0CcEwVgYuAplS7Z+Etvr7NJqX2ADvVjBwKTDbflG2qsGh2JNCpjffnol1Rf7Yvt5fiGAh6aSlBtFzM+AgNbFWp3ntocSe5MxuhL40/kFWUNeVPBrTd+OyFdEW2sXjfTua/2dvZcGjWJUjOgFc3tZ9/HXC/KRDi4yjtVwIjXgvNlRsI0g1",

    'default_return_type'    => 'json',

];