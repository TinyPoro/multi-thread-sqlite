# Thực hiện việc ghi đa luồng trên SQLite gần như cùng một thời gian / Hiệu suất của mỗi luồng có mức độ gần như bằng nhau


Chào các bạn,

Như bạn đã biết thì SQLite là một cơ sở dữ liệu đơn luồng mặc định là được đưa vào hệ điều hành linux. Có rất nhiều các công trình nghiên cứu về việc sử dụng SQLite để lưu trữ dữ liệu. Cũng có rất nhiều công trình nghiên cứu về việc làm cách nào có thể truy cập vào cơ sở dữ liệu SQLite cho các hoạt động ghi một cách đa luồng. Tôi sẽ chia sẻ nghiên cứu nhỏ của tôi về việc làm cách nào đạt được thao tác ghi đa luồng trên cơ sở dũ liệu SQLite.

Chúng ta cần biết về các ưu điểm và nhược điểm của SQLite

#### Ưu điểm.
 * SQLite được viết bằng ngôn ngữ lập trình C thuần. vì vậy nó truy cập ổ đĩa hoặc cơ sở dữ liệu trong bộ nhớ hoặc các tiến trình dữ liệu là nhanh nhất. Hãy thử nghĩ khi bạn sử dụng ổ đĩa SSD.
 * SQLite hỗ trợ cả trên bộ nhớ ( hỗ trợ trên Ram ). Trên memory, SQLite có thể nhanh hơn gần gấp đôi. Nếu bạn có thể hiểu vấn đề về phân trang. Nó cũng đủ nhanh rồi.
* SQLite chỉ có luồng đơn. Vì vậy nguy cơ dữ liệu bị hỏng là thấp nhất.
* Cơ sở dữ liệu của SQLite chỉ nằm trong 1 file duy nhất. Vì vậy có thể di chuyển cơ sở dữ liệu và truy cập bởi bất cứ nền tảng nào một cách rất dễ dàng. 
* SQLite là trình quản trị cho người dùng cuối.
* Nền tảng chéo - SQLite có thể sử dụng cho mọi nền tảng OS chính.
* OPENSOURCE OPENSOURCE OPENSOURCE !!!
* Và nhiều hơn nữa ………..

#### Vài nhược điểm.
* Như chúng ta đã đề cập SQLite chỉ có một luồng đơn. Vì vậy có nghĩ là SQLite chỉ có thể thực hiện một thao tác ghi trong cùng một thời điểm.
* Như đã đề cập thì SQLite lưu trữ [cơ sở] dữ liệu trong một file. Vì vậy có nghĩa là toàn bộ cơ sở dữ liệu bị khoá trong quá trình ghi. điều này không được mong muốn đối với việc truy cập một cơ sở dữ liệu lớn và chuyên sâu. 
* Không yêu cầu xác thực ở mức ứng dụng.

#### thực hiện việc ghi đa luồng gần như đồng thời

Bây giờ tôi sẽ cố gắng đưa ra một vài trick nhỏ để thực hiện thao tác ghi gần như cùng một thời điểm.

##### Chú ý: SQLite không bao giờ cho phép bạn hoàn tất ROW LEVEL LOCK. Vì vậy bạn đừng nên phí thời gian để tìm kiếm nó.

Đương nhiên nó có thể tác động tới hiệu năng nếu bạn thực hiện hành động ghi voà phần lớn của bảng. nhưng luồng thứ hai sẽ không phải chờ mất nhiều thời gian để thao tác đầu tiên kết thúc.

![image](0.jpeg)

#### Đây là một điểm quan trọng của STEP 1.7.

Vì như bạn biết kiểu B-TREE đánh index rất nhanh. và thao tác GHI của bạn sẽ thực hiện theo mỗi ROWID được lập chỉ mục mặc định

Ví dụ:

```
delete from table where name='Fariz' // nó sẽ khoá toàn bộ cơ sở dữ liệu chính trong khoảng 10 giây.
```

##### REPLACE WITH:

Chèn vào bộ nhớ đệm. Khoá rowid được chọn, 'processid' from table where name = 'Fariz'; // nó chỉ tạm thời khoá cơ sở dữ liệu trong 10s.

delete from table where ROWID in ( select rowid from tepm.Lock where name='fariz' and processid='XYZ' ) // thao tác delete sẽ khoá file DB trong khoảng 0.001s.

Tôi đã thực hiện một test nhỏ và kết quả khá tốt. Tôi đã có thể hoàn thành 2 thao tác update tương tự trong 11s mà mỗi thao tác mất khoảng 10s, cho bảng có khoảng 10 triệu row.

Hi vọng là nó sẽ giúp được có các người dùng SQLite.