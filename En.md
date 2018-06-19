# Thực hiện việc ghi đa luồng trên SQLite gần như cùng một thời gian / Hiệu suất của mỗi luồng có mức độ gần như bằng nhau


Chào các bạn,

Như bạn đã biết thì SQLite là một cơ sở dữ liệu đơn luồng mặc định là được đưa vào hệ điều hành linux. Có rất nhiều các công trình nghiên cứu về việc sử dụng SQLite để lưu trữ dữ liệu. Cũng có rất nhiều công trình nghiên cứu về việc làm cách nào có thể truy cập vào cơ sở dữ liệu SQLite cho các hoạt động ghi một cách đa luồng. Tôi sẽ chia sẻ nghiên cứu nhỏ của tôi về việc làm cách nào đạt được thao tác ghi đa luồng trên cơ sở dũ liệu SQLite.

Chúng ta cần biết về các ưu điểm và nhược điểm của SQLite

#### Ưu điểm.
 * SQLite được viết bằng ngôn ngữ lập trình C thuần. vì vậy nó truy cập ổ đĩa hoặc cơ sở dữ liệu trong bộ nhớ hoặc các tiến trình dữ liệu là nhanh nhất. Hãy thử nghĩ khi bạn sử dụng ổ đĩa SSD.
 * SQLite hỗ trợ cả trên bộ nhớ ( hỗ trợ trên Ram ). Trên memory, SQLite có thể nhanh hơn gần gấp đôi. Nếu bạn có thể hiểu vấn đề về phân trang. Nó cũng đủ nhanh rồi.
* SQLite chỉ có luồng đơn. Vì vậy nguy cơ dữ liệu bị hỏng là thấp nhất.
* Cơ sở dữ liệu của SQLite chỉ nằm trong 1 file duy nhất. Vì vậy có thể di chuyển cơ sở dữ liệu và truy cập bởi bất cứ nền tảng nào một cách rất dễ dàng. 
* SQLite is 0 administration for end user. 
* Cross platform. SQLite can be used on all major OS platform. 
* OPENSOURCE OPENSOURCE OPENSOURCE !!!
* And so on ………..

#### Vài nhược điểm.
* As we mentioned SQLite is single thread. So it means SQLite can do one write operation at the same time. 
* As we mentioned SQLite keep data [base] in one file. So it means whole database locks during write operation. This very unwanted for huge and intensive access database. 
* No application level authentication required.

#### thực hiện việc ghi đa luồng gần như đồng thời

Now I will try to show small trick to make write operation in almost same time.

##### Chú ý: SQLite không bao giờ cho phép bạn hoàn tất ROW LEVEL LOCK. Vì vậy bạn đừng nên phí thời gian để tìm kiếm nó.

Of course it can impact performance if you do write operation on big part of table. But second thread will not wait a lot of time for first operation end. 

![image](0.jpeg)

#### here is main point STEP 1.7.

Because as you know B TREE index is super fast. And your WRITE operation will do according for each ROWID which is indexed by default.

for exaample:

delete from table where name='Fariz' // it will lock whole main database for 10 sec.

##### REPLACE WITH:

insert into tepm.Lock select rowid,'processid' from table where name = 'Fariz'; // it will lock only temporary attached database for 10 sec.

delete from table where ROWID in ( select rowid from tepm.Lock where name='fariz' and processid='XYZ' ) // delete will lock DB file for 0.001 second.

I have done small test and result is great for me. I was able to finish 2 same update operation in 11 second which is each transaction takes 10 sec. for table which include is 40 million row.

Hopefully it will help for SQLite users.