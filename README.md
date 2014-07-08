UCenter 集成插件
====
這是用於 Typecho 的插件，  
本插件會在用戶登入後，  
查詢 UCenter 中是否存在該用戶，  
若該用戶存在 UCenter ，就將該用戶寫入 Typecho 資料庫中，  
並直接登入 Typecho ，  
若是，該用戶不存在於 UCenter 的場合，  
才會檢查 Typecho 資料庫的用戶表。  

TODO
====
本插件還未完成，還有諸多部分需要改善，  
歡迎有興趣的朋友參與:P  

1. 需要接收 UCenter 的請求，如刪除用戶等操作。
2. UCenter 的 uid 與 Typecho 的 uid 沒有進一步地關聯。

