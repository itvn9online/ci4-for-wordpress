### Custom views
Thư mục chứa các view riêng của từng website, khi có file view trong thư mục này trùng tên với view trong thư mục `/app/Views/default` thì view trong này sẽ được kích hoạt thay vì dùng trong thư mục `/app/Views/default`

#### Các thư mục hỗ trợ ghi đè
- /app/Views/admin
	- /app/Views/admin/default
- /app/Views/default
- /app/Views/html

#### Trường hợp không muốn ghi đè kiểu custom:
- Copy file trong thư mục `/app/Views/default` bỏ ra thư mục `/app/Views` rồi code như bình thường thôi. Cách này bỏ qua được cái bước `if else` để include file view nhưng có thể sẽ mất đi một số tính năng mặc định hay ho của bản gốc.

> Lưu ý: nên tạo một project khác, mỗi khi update code thì up code ở bản gốc trước, sau đó mới update code ở project nhánh để thực hiện ghi đè file code riêng.
