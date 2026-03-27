<?php

$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_curriculum.json';
if (!file_exists($json_path)) {
  echo "Curriculum JSON not found: $json_path\n";
  exit(1);
}

// Khai báo sẵn toàn bộ nội dung FAQ cho 22 khóa học (ID 26 đến 47)
$faqs_map = [
  26 => [
    ['question' => 'Khóa học này dành cho đối tượng nào?', 'answer' => 'Khóa học được thiết kế đặc biệt dành cho các nhà lãnh đạo, quản lý cấp cao và chủ doanh nghiệp muốn tìm hiểu về AI mà không cần nền tảng kỹ thuật lập trình.'],
    ['question' => 'Tôi có cần biết code để học khóa này không?', 'answer' => 'Hoàn toàn không. Nội dung tập trung vào cách ứng dụng thuật toán AI vào thực tế quản lý và đề xuất chiến lược thay vì đi sâu vào mặt kỹ thuật.'],
    ['question' => 'Khóa học cung cấp những kiến thức gì cốt lõi?', 'answer' => 'Cung cấp góc nhìn tổng quan về trí tuệ nhân tạo, lịch sử, các khái niệm cơ bản và những tác động to lớn của nó đối với lộ trình doanh nghiệp hiện đại.'],
    ['question' => 'Tôi sẽ nhận được chứng chỉ sau khi hoàn thành chứ?', 'answer' => 'Có, bạn sẽ được cấp chứng chỉ hoàn thành khóa học từ EverLMS để ghim vào hồ sơ năng lực của mình.'],
    ['question' => 'Ứng dụng thực tế của kiến thức này là gì?', 'answer' => 'Bạn sẽ có khả năng nhận diện những cơ hội triển khai AI ban đầu tại chính bộ phận làm việc hoặc doanh nghiệp của bạn.']
  ],
  27 => [
    ['question' => 'Tôi sẽ học những công nghệ AI nào trong khóa này?', 'answer' => 'Bạn sẽ tìm hiểu các nhánh chính của AI bao gồm Machine Learning (học máy), Deep Learning (Học sâu), và Natural Language Processing (Xử lý ngôn ngữ tự nhiên).'],
    ['question' => 'Các công nghệ này có tác động gì tới kinh doanh?', 'answer' => 'Chúng thay đổi tự động hóa quy trình, tăng cường độ chính xác khi dự báo tài chính, tối ưu hóa dịch vụ khách hàng từ chatbot đến phân tích chân dung người dùng.'],
    ['question' => 'Khóa học có đề cập tới Generative AI không?', 'answer' => 'Có, một phần của khóa học sẽ phân tích tác động đáng kể của Generative AI (như ChatGPT, Midjourney) đến sáng tạo và tiếp thị.'],
    ['question' => 'Làm sao để biết công nghệ nào phù hợp với công ty tôi?', 'answer' => 'Khóa học sẽ cung cấp các khung đánh giá (frameworks) để bạn so sánh giữa chi phí, lợi ích và mức độ phù hợp công nghệ.'],
    ['question' => 'Có cần cài đặt phần mềm nào khi học không?', 'answer' => 'Không cần cài đặt gì, khóa học mang tính chất lý luận kinh doanh trên hệ thống lý thuyết thực chiến.']
  ],
  28 => [
    ['question' => 'Tại sao cần phải học về Đạo đức AI?', 'answer' => 'Vì AI tạo ra những rủi ro bảo mật sinh trắc, định kiến từ dữ liệu và các vấn đề pháp lý. Quản lý AI đạo đức giúp thương hiệu tránh khủng hoảng.'],
    ['question' => 'Khóa học giúp tôi giải quyết thiên vị trong AI như thế nào?', 'answer' => 'Chúng ta sẽ học cách nhận diện thiên vị (bias) trong bộ dữ liệu huấn luyện và các nguyên tắc thiết kế AI để đảm bảo tính minh bạch.'],
    ['question' => 'Các khung pháp lý liên quan sẽ được đề cập là gì?', 'answer' => 'Bạn sẽ tìm hiểu tổng quan về GDPR và các tiêu chuẩn bảo vệ dữ liệu toàn cầu liên quan trực tiếp đến AI sinh tạo.'],
    ['question' => 'AI có lấy đi công việc của nhân viên không?', 'answer' => 'Khóa học sẽ bàn về khía cạnh Trách nhiệm xã hội, cách để AI augmentation (hỗ trợ) thay vì replacement (thay thế) hoàn toàn con người.'],
    ['question' => 'Làm sao để xây dựng chính sách AI cho công ty?', 'answer' => 'Trong quy trình khóa học, bạn sẽ có các biểu mẫu và hướng dẫn để tự soạn thảo cẩm nang nguyên tắc sử dụng AI nội bộ.']
  ],
  29 => [
    ['question' => 'Quy trình phát triển một chiến lược AI bắt đầu từ đâu?', 'answer' => 'Luôn bắt đầu từ mục tiêu kinh doanh. Khóa học sẽ hướng dẫn bạn kỹ năng Audit (đánh giá) lại hiện trạng doanh nghiệp trước khi quyết định đầu tư AI.'],
    ['question' => 'Có những chiến lược AI cơ bản nào?', 'answer' => 'Từ tự động hóa dịch vụ khách hàng, tối ưu vận hành nội dung, cho đến tạo ra sản phẩm hoàn toàn mới dựa vào mô hình AI độc quyền.'],
    ['question' => 'Phân tích ROI cho dự án AI được tính ra sao?', 'answer' => 'Chúng tôi cung cấp các công thức tài chính để đo lường lợi nhuận trên chi phí dành cho các dự án tích hợp phần mềm thông minh.'],
    ['question' => 'Khóa học có case study thực tế không?', 'answer' => 'Chắc chắn rồi. Chúng tôi mổ xẻ các chiến lược chuyển đổi số AI thành công của các tập đoàn vừa và nhỏ.'],
    ['question' => 'Làm sao thuyết phục ban lãnh đạo rót vốn cho AI?', 'answer' => 'Khóa học hướng dẫn bạn tạo bản báo cáo đề xuất (Pitch Deck) với góc nhìn thực tế về quản trị rủi ro để chốt ngân sách.']
  ],
  30 => [
    ['question' => 'Khóa này có học cách chạy quảng cáo AI không?', 'answer' => 'Có, nội dung sẽ lướt qua các nền tảng tự động hóa digital marketing hiện đại hỗ trợ bởi AI.'],
    ['question' => 'Làm sao AI tối ưu được trải nghiệm khách hàng?', 'answer' => 'Qua cá nhân hóa trải nghiệm diện rộng (hyper-personalization) và xây dựng hệ thống chatbot chăm sóc 24/7.'],
    ['question' => 'Kiến thức có khó áp dụng cho doanh nghiệp nhỏ không?', 'answer' => 'Rất dễ dàng, các công cụ được giới thiệu có thể sử dụng dưới dạng SaaS chi phí vô cùng tiết kiệm.'],
    ['question' => 'Khóa học có nói về SEO AI không?', 'answer' => 'Có, một chương tập trung vào việc tạo nội dung tự động bằng AI và phương pháp tối ưu hóa công cụ tìm kiếm.'],
    ['question' => 'Yêu cầu đầu vào của khóa học?', 'answer' => 'Biết cơ bản về quy trình Marketing và Chăm sóc khách hàng của doanh nghiệp mình.']
  ],
  31 => [
    ['question' => 'AI giúp ích gì cho vận hành tự động hóa?', 'answer' => 'Giảm rủi ro thao tác sai, tiết kiệm 70% thời gian xử lý dữ liệu và tự động phân loại hồ sơ thông minh.'],
    ['question' => 'RPA và AI khác nhau như thế nào?', 'answer' => 'RPA tự động quy trình theo luật định, còn AI cung cấp nhận thức, hiểu ngữ cảnh để RPA trở nên thông minh hơn. Khóa học sẽ phân tách rõ.'],
    ['question' => 'Ngành sản xuất có ứng dụng được kiến thức này không?', 'answer' => 'Rất tốt, khóa học cung cấp tư duy áp dụng AI vào quản lý chuỗi cung ứng, dự báo hàng hóa tồn kho và theo dõi bảo trì máy móc.'],
    ['question' => 'Hệ thống tự động hóa AI có chi phí đắt đỏ?', 'answer' => 'Tùy quy mô. Việc triển khai các automation API đơn giản không hề tốn kém, khóa học sẽ phân bổ các cấp độ đầu tư khác nhau.'],
    ['question' => 'Thời lượng khóa học là bao nhiêu?', 'answer' => 'Khoảng 4-6 giờ theo mức độ học chuẩn, hoàn toàn có tính ứng dụng tức thì.']
  ],
  32 => [
    ['question' => 'Knowledge Management bằng AI là gì?', 'answer' => 'Là sử dụng trí tuệ nhân tạo để lập chỉ mục, lưu trữ và tự động truy xuất tài liệu nội bộ (Intranet) nhanh chóng.'],
    ['question' => 'Khóa học có hướng dẫn dùng AI cho đội nhóm không?', 'answer' => 'Có, phân tích các tính năng AI tích hợp trên Slack, Teams, Notion giúp làm việc tương tác hiệu quả.'],
    ['question' => 'Bảo mật thông tin nội bộ thế nào khi dùng AI?', 'answer' => 'Khóa học phân tích rõ khái niệm Private AI Model và giới hạn data chia sẻ ra ngoài.'],
    ['question' => 'Sau khóa học tôi tự xây dựng hệ Wiki AI được không?', 'answer' => 'Bạn sẽ nắm trọn quy trình khái niệm và biết cách chọn nhà cung cấp tốt nhất để build.'],
    ['question' => 'Kiến thức có bị lỗi thời nhanh không?', 'answer' => 'Không, tư duy quản lý tri thức là dài hạn, công cụ chỉ là yếu tố thay đổi.']
  ],
  33 => [
    ['question' => 'Khóa học yêu cầu phải biết SQL không?', 'answer' => 'Không bắt buộc. Trọng tâm là việc ứng dụng AI thay thế mã code để xử lý data bằng ngôn ngữ tự nhiên.'],
    ['question' => 'Business Intelligence (BI) có khác gì AI Analytics?', 'answer' => 'BI truyền thống trả lời "Điều gì đã xảy ra", còn AI trả lời "Điều gì sắp xảy ra". Bạn sẽ học về Predictive Analytics.'],
    ['question' => 'Khóa học có bao gồm hướng dẫn PowerBI hoặc Tableau?', 'answer' => 'Chúng tôi giới thiệu khái quát và đi sâu vào tính năng nhúng AI trong các platform này.'],
    ['question' => 'Tôi có thể phân tích dữ liệu bán hàng nội bộ chứ?', 'answer' => 'Chính xác, đó là một trong những bài toán (use case) chính trị xuyên suốt khóa học.'],
    ['question' => 'Cần chuẩn bị lượng dữ liệu bao lớn để thực hành?', 'answer' => 'Khóa học cung cấp các bộ dummy data (dữ liệu mẫu) từ hàng chục ngàn dòng để phục vụ học tập.']
  ],
  34 => [
    ['question' => 'Khóa học này dành cho chuyên gia IT hay Quản lý?', 'answer' => 'Khóa học cung cấp góc nhìn toàn cảnh về rủi ro mạng dành cho cấp độ quản lý và những người ra quyết định công nghệ.'],
    ['question' => 'AI giúp ngăn chặn tấn công mạng như thế nào?', 'answer' => 'Qua việc phát hiện bất thường (anomaly detection) theo thời gian thực và phân tích hành vi hacker tự động.'],
    ['question' => 'Tôi sẽ học về bảo mật hệ thống nào?', 'answer' => 'Overview các hệ thống đám mây (Cloud Security), mạng lưới và điểm bảo vệ cuối (Endpoints).'],
    ['question' => 'Hacker cũng dùng AI phải không?', 'answer' => 'Đúng vậy, khóa học dành cả một chương để nói về AI-Driven Attacks để bạn chuẩn bị phương án đối phó.'],
    ['question' => 'Nội dung có liên kết chuẩn ISO bảo mật không?', 'answer' => 'Có lồng ghép cơ bản tư duy của bộ tiêu chuẩn đánh giá bảo mật thông tin quốc tế ISO 27001.']
  ],
  35 => [
    ['question' => 'Data Fundamentals nghĩa là sao?', 'answer' => 'Ý là nền tảng dữ liệu để phục vụ cho các mô hình ngôn ngữ lớn (LLMs). Không có data tốt thì AI là vô dụng.'],
    ['question' => 'Có cần tính toán toán học phức tạp không?', 'answer' => 'Không, khóa học thuần về tư duy kiến trúc và quy trình quản trị data.'],
    ['question' => 'Khác biệt giữa Data Lake và Data Warehouse là gì?', 'answer' => 'Khóa học sẽ có bài học giải thích chi tiết, ưu nhược điểm và lựa chọn nào phù hợp cho mô hình AI.'],
    ['question' => 'Việc mua data bên thứ 3 có được bàn luận?', 'answer' => 'Có, đánh giá Data Third-party là 1 kỹ năng quan trọng được huấn luyện.'],
    ['question' => 'Có công cụ trực quan nào được áp dụng không?', 'answer' => 'Chúng tôi sẽ minh hoạ bằng các sơ đồ kiến trúc dữ liệu trực quan rất dễ hiểu.']
  ],
  36 => [
    ['question' => 'Chuẩn bị dữ liệu (Data Prep) chiếm bao nhiêu thời gian dự án AI?', 'answer' => 'Thường chiếm 70-80% tổng quỹ lượng thời gian. Khóa học xoáy vào việc tối ưu công đoạn này.'],
    ['question' => 'Cleaning Data (Làm sạch dữ liệu) học ở đâu?', 'answer' => 'Nằm trọng tâm trong khóa học này. Xử lý missing values (giá trị thiếu) và nhiễu data.'],
    ['question' => 'Khóa được thiết kế dưới dạng thực hành hay lý thuyết?', 'answer' => 'Là sự kết hợp 50/50. Cung cấp phương pháp luận và các template tài liệu phân loại dữ liệu.'],
    ['question' => 'Xử lý data hình ảnh hay data văn bản?', 'answer' => 'Tập trung chủ yếu vào Data Text/Tabular (bảng biểu) để phục vụ chuyển đổi doan nghiệp truyền thống.'],
    ['question' => 'Có công cụ AI giúp tự làm sạch dữ liệu không?', 'answer' => 'Có, sẽ có bài nói về các engine làm sạch tự động trên Cloud.']
  ],
  37 => [
    ['question' => 'Văn hóa dữ liệu (Data Culture) bắt nguồn từ đâu?', 'answer' => 'Từ cấp độ lãnh đạo cao nhất. Khóa học cung cấp chiến thuật lan tỏa phong trào Data-Driven xuống nhân viên.'],
    ['question' => 'Làm sao giải quyết sức ỳ kháng cự của nhân sự? ', 'answer' => 'Sử dụng các bài tập thay đổi quản trị (Change Management), thay đổi thói quen báo cáo số liệu cảm tính.'],
    ['question' => 'Tiêu chí đo lường văn hóa dữ liệu thành công?', 'answer' => 'Sự gia tăng năng suất và mức độ sẵn sàng tra cứu số liệu thật trong các cuộc họp thay vì phỏng đoán.'],
    ['question' => 'Việc huấn luyện nội bộ công ty tiến hành thế nào?', 'answer' => 'Ban lãnh đạo sẽ có sẵn quy trình mẫu huấn luyện trong Module áp dụng thực tế của khóa này.'],
    ['question' => 'Tại sao văn hóa này ảnh hưởng trực tiếp đến thành công AI?', 'answer' => 'Vì nhân viên ghi nhận dữ liệu sai từ đầu sẽ khiến mô hình AI dự báo sai bét (Garbage in, Garbage out).']
  ],
  38 => [
    ['question' => 'Tôi có cần học Cloud ở Google, AWS hay Azure?', 'answer' => 'Chúng tôi sẽ giới thiệu tổng quan bộ ba nền tảng này và các dịch vụ AI API tích hợp sẵn của chúng.'],
    ['question' => 'Cloud AI có đắt đỏ cho công ty siêu nhỏ không?', 'answer' => 'Bạn sẽ học mô hình Pay-As-You-Go, kiểm soát chi phí từng đồng, dùng bao nhiêu trả bấy nhiêu.'],
    ['question' => 'Có được học cách Deploy (triển khai) Model lên Cloud?', 'answer' => 'Chủ yếu học mặt khái niệm lộ trình triển khai. Không yêu cầu gõ dòng lệnh DevOps.'],
    ['question' => 'Tôi có nên tự build server Local ở nhà thay vì Cloud?', 'answer' => 'Khóa học dùng một bài so sánh On-Premise vs Cloud để bạn tự trả lời câu hỏi này.'],
    ['question' => 'Bảo mật dữ liệu trên Cloud có đáng lo ngại?', 'answer' => 'Các chiến lược quản trị IAM, phân quyền dự án AI tiêu chuẩn sẽ giải quyết nỗi sợ này.']
  ],
  39 => [
    ['question' => 'Thị trường có hàng ngàn Tool AI, khóa học giúp gì?', 'answer' => 'Giúp bạn có bản đồ "Radar" phân loại, sàng lọc những tool scam và tool chất lượng thật sự.'],
    ['question' => 'Các tools này có được cập nhật thường xuyên không?', 'answer' => 'Tư duy đánh giá tool là vĩnh cửu dù cho phần mềm có đổi mới. Chúng tôi dạy bạn cách chọn.'],
    ['question' => 'Khóa học có cung cấp checklist mua sắm phần mềm?', 'answer' => 'Có, một bản checklist đánh giá (Vendor Evaluation Form) để dùng làm biểu mẫu mua Tool AI.'],
    ['question' => 'Open-source (mã nguồn mở) AI hay trả phí SaaS tốt hơn?', 'answer' => 'Tùy thuộc nghiệp vụ doanh nghiệp. Có một bài học riêng về chiến lược Make vs Buy (Tự làm hay mua).'],
    ['question' => 'Làm sao để biết Tool đó không thu thập dữ liệu khách hàng?', 'answer' => 'Chúng ta sẽ học cách đọc hiểu điều khoản (TOS/Privacy) nhanh gọn liên quan tới việc huấn luyện AI.']
  ],
  40 => [
    ['question' => 'Các ngành nghề nào sẽ được phân tích case study?', 'answer' => 'Đa dạng từ Bán lẻ (Retail), Y tế (Healthcare), Bất động sản, Dịch vụ tài chính, và Sản xuất.'],
    ['question' => 'Có Case Study nào ở Việt Nam hoặc Đông Nam Á không?', 'answer' => 'Bên cạnh Big Tech, khóa học cố gắng cập nhật bối cảnh chuyển đổi AI đặc thù của công ty vừa ở châu Á.'],
    ['question' => 'Bài học rút ra đằng sau những dự án thất bại là gì?', 'answer' => 'Chúng tôi mổ xẻ cả 2 mặt thành công vang dội và "đốt tiền" thảm bại để bạn rút kinh nghiệm xương máu.'],
    ['question' => 'Người học có cơ hội tự phân tích thực hành không?', 'answer' => 'Có bài tập tình huống thực tiễn mô phỏng (Simulation) để vạch chiến lược cho 1 công ty ảo.'],
    ['question' => 'Tài liệu phân tích dài và khó đọc?', 'answer' => 'Tất cả Case Study đã được đơn giản hóa thành biểu đồ số liệu trực quan cao, tiết kiệm thời gian tiếp thu.']
  ],
  41 => [
    ['question' => 'Quản trị sự thay đổi (Change Management) là học cái gì?', 'answer' => 'Học tâm lý học và cách trấn an người lao động trước sự thâm nhập của hệ thống tự động hóa AI.'],
    ['question' => 'Tỉ lệ thất bại dự án AI do con người hay máy móc?', 'answer' => 'Hơn 70% thất bại do con người và văn hóa không tiếp nhận. Đó là lí do khóa này cực quan trọng.'],
    ['question' => 'Làm sao để AI Adoption (Hấp thụ AI) xảy ra tự nhiên?', 'answer' => 'Phát triển các hạt giống Change Agents (nhân sự tiêu biểu) hỗ trợ đồng nghiệp sử dụng AI hằng ngày.'],
    ['question' => 'Giao tiếp lúc triển khai dự án như thế nào cho khéo?', 'answer' => 'Có một khung kịch bản truyền thông nội bộ giúp gắn kết mọi bộ phận chung 1 tầm nhìn duy nhất.'],
    ['question' => 'Tôi nên áp dụng AI nhanh một lúc hay từng phòng ban?', 'answer' => 'Khái niệm "Pilot Project" (Triển khai thí điểm khu vực) sẽ là đáp án chi tiết dành cho bạn.']
  ],
  42 => [
    ['question' => 'Reskilling và Upskilling khác nhau chỗ nào?', 'answer' => 'Upskill là nâng cao trình độ. Reskill là dạy kỹ năng mới hoàn toàn khi công việc cũ bị AI đào thải.'],
    ['question' => 'Ngành nhân sự (HR) có ứng dụng ngay bài học này?', 'answer' => 'Đây là cẩm nang bắt buộc cho giám đốc nhân sự trong kỷ nguyên trí tuệ nhân tạo.'],
    ['question' => 'Chi phí đào tạo cấp tốc quá cao thì làm sao?', 'answer' => 'Chúng tôi hướng dẫn mô hình tận dụng chính AI để đào tạo nhân viên sử dụng AI (Microlearning).'],
    ['question' => 'Cần ưu tiên kỹ năng nào trong 3 năm tới?', 'answer' => 'Prompt Engineering (gõ lệnh AI), Critical Thinking và Khả năng xử lý rủi ro của hệ thống tự động.'],
    ['question' => 'Đánh giá năng lực nhân viên sau đào tạo như thế nào?', 'answer' => 'Đo lường bằng KPI tăng trưởng và sự giảm bớt các thao tác thủ công Excel truyền thống.']
  ],
  43 => [
    ['question' => 'Center of Excellence (CoE) trong doanh nghiệp là gì?', 'answer' => 'Là trung tâm xuất sắc: Một nhóm liên chức năng (Cross-functional) tập hợp nhân tài AI định hướng toàn công ty.'],
    ['question' => 'Thành viên trong Ban CoE này gồm những ai?', 'answer' => 'Cần có Data Scientist, Business Analyst, Legal, và IT Management đứng ra bảo trợ chất lượng.'],
    ['question' => 'Doanh nghiệp siêu nhỏ có cần xây CoE?', 'answer' => 'Có thể áp dụng mô hình "CoE tinh gọn" gồm 2-3 người làm hạt nhân công nghệ tư vấn liên phòng ban.'],
    ['question' => 'Cấp quản lý CoE cần những KPI cốt lõi nào?', 'answer' => 'Tỉ lệ sáng kiến AI sinh lời, mức độ ứng dụng Cloud thành công, và tối ưu hóa vận hành nội bộ.'],
    ['question' => 'Việc xây dựng nhóm này có tốn chi phí thuê nhân tài?', 'answer' => 'Khóa học hướng dẫn sử dụng chuyên gia bên ngoài (Vendor) kết hợp nhân lực nội tại để làm giảm ngân sách.']
  ],
  44 => [
    ['question' => 'Tại sao cần học mường tượng tương lai The Future of AI?', 'answer' => 'Để không bị bỏ lại phía sau bởi các đối thủ cập nhật xu hướng nhanh nhạy (Future Proofing).'],
    ['question' => 'Cụm từ AGI (Artificial General Intelligence) có được nhắc tới?', 'answer' => 'Có, sự trỗi dậy của AGI và Agentic Workflow sẽ thay đổi tận gốc khái niệm vận hành tập đoàn.'],
    ['question' => 'Mô hình kinh doanh truyền thống sẽ chết?', 'answer' => 'Không chết, nhưng sẽ tiến hóa. Khóa học giúp vạch ra chu kỳ phát triển 5 - 10 năm tiếp theo.'],
    ['question' => 'Liệu có "Mùa đông AI" (AI Winter) lần nữa không?', 'answer' => 'Những rào cản tính phí tài nguyên và vỡ bong bóng cổ phiếu công nghệ sẽ được nhìn nhận khách quan.'],
    ['question' => 'Video/Audio thực tế ảo AI đóng vai trò gì?', 'answer' => 'Chúng trở thành trung tâm của trải nghiệm siêu tương tác Metaverse thế hệ mới trong kinh doanh M2C.']
  ],
  45 => [
    ['question' => 'Emerging AI Trends nào hot nhất hiện nay?', 'answer' => 'Edge AI (AI vùng biên cục bộ không cần mạng), Large Action Models (LAMs) hỗ trợ thực thi tác vụ trực tiếp.'],
    ['question' => 'Công nghệ lượng tử (Quantum) và AI có kết hợp không?', 'answer' => 'Khóa học cung cấp góc tóm tắt cơ bản nhất cho Quản trị viên nắm bắt làn sóng Quantum Machine Learning.'],
    ['question' => 'Các startup công nghệ mới có đất sống cạnh tranh với Google/AI?', 'answer' => 'Xu hướng Small Language Models (SLMs) siêu tùy chỉnh chuyên sâu từng ngành là cơ hội vàng của startup.'],
    ['question' => 'Sự kiện và sách nên theo dõi thường xuyên là gì?', 'answer' => 'Khóa học cung cấp danh mục newsletter uy tín (HuggingFace, ArXiv) để tự bạn cập nhật tri thức mỗi ngày.'],
    ['question' => 'Blockchain và AI có được đề cập?', 'answer' => 'Có một chương về Web3 x AI, đảm bảo dữ liệu data huấn luyện cho AI là bất biến, chống giả mạo DeepFake.']
  ],
  46 => [
    ['question' => 'Thế nào là Làn sóng AI thứ 3 (Next Wave)?', 'answer' => 'Là khi AI tiến thẳng vào khả năng suy luận Logic sâu và hệ thống đa tác vụ tự điều hướng hành vi (Agents).'],
    ['question' => 'Làm sao công ty thiết kế lộ trình dài hạn đón sóng?', 'answer' => 'Chuẩn bị Data Sạch, củng cố rào cản pháp lý và chuyển đổi hạ tầng 100% sang Hybrid Cloud linh hoạt.'],
    ['question' => 'Hợp đồng thông minh tác động tới pháp lý nội bộ ra sao?', 'answer' => 'Nền tảng của các Agent đàm phán hợp đồng thay cho con người. Bạn sẽ nắm bắt khái niệm Smart Legal AI.'],
    ['question' => 'Tác động từ những quy định sắp phát hành ở liên minh EU/Mỹ?', 'answer' => 'Ai Act của EU sẽ trở thành điểm nghẽn hoặc tiêu chuẩn vàng. Doanh nghiệp bạn cần biết để tuân thủ sớm.'],
    ['question' => 'Bài học cốt lõi khóa này mang lại?', 'answer' => 'Từ bỏ tư duy ứng phó tình thế, thay vào đó là tư duy xây dựng kiến trúc mở, cho phép dễ dàng cấy ghép kỹ thuật AI mới.']
  ],
  47 => [
    ['question' => 'Bản kế hoạch AI Roadmap cuối cùng là gì?', 'answer' => 'Là bài thi tốt nghiệp: Bạn sẽ xây dựng lộ trình thực thi dự án AI chiến lược cho chính công ty thực tế của bạn.'],
    ['question' => 'Roadmap này cần khoảng bao nhiêu trang tài liệu?', 'answer' => 'Khóa học có sẵn Template 30 slide chiến lược rút gọn, giúp bạn thuyết trình thẳng trước Ban Giám Đốc.'],
    ['question' => 'Phần thi có hỗ trợ Review bởi AI không?', 'answer' => 'Có, chính hệ thống AI tích hợp trong EverLMS sẽ cung cấp feedback trực tiếp vào cấu trúc tư duy bạn vạch ra.'],
    ['question' => 'Các nguồn lực (Resource) tài chính được hoạch định chi tiết chứ?', 'answer' => 'Bảng ngân sách ảo nhưng bám sát giá trị thị trường API năm hiện tại được cung cấp đầy đủ.'],
    ['question' => 'Sau khóa học tôi nhận danh hiệu gì?', 'answer' => 'Chứng chỉ "AI Business Strategist Master" minh chứng khả năng triển khai Roadmap số hóa chuyên sâu thành công.']
  ]
];

$curriculum = json_decode(file_get_contents($json_path), TRUE);
$updated = false;

foreach ($curriculum as &$cat) {
  if (empty($cat['courses'])) continue;
  
  foreach ($cat['courses'] as &$course) {
    if (!empty($course['id'])) {
      $cid = $course['id'];
      if (isset($faqs_map[$cid])) {
        // Chỉ ghi đè nếu FAQ chưa được update
        if (empty($course['is_faq_generated']) || empty($course['faq'])) {
          $course['faq'] = $faqs_map[$cid];
          $course['is_faq_generated'] = true;
          $updated = true;
          echo "Đã thêm FAQ thủ công cho khóa học ID $cid\n";
        } else {
          echo "Bỏ qua ID $cid vì FAQ đã có.\n";
        }
      }
    }
  }
}

if ($updated) {
  file_put_contents($json_path, json_encode($curriculum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  echo "\nHoàn tất! Đã lưu toàn bộ toàn bộ FAQ chất lượng cao vào ai_curriculum.json siêu tốc bằng năng lực nội tại.\n";
} else {
  echo "\nKiểm tra xong, không có cập nhật nào cần thiết.\n";
}

