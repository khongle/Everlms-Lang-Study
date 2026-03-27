<?php

$json_path = \Drupal::root() . '/modules/custom/lms_ai/JSON/ai_curriculum.json';
if (!file_exists($json_path)) {
  echo "Curriculum JSON not found: $json_path\n";
  exit(1);
}

// 22 Courses English FAQs
$faqs_map = [
  26 => [
    ['question' => 'Who is this course designed for?', 'answer' => 'It is tailored for business leaders, executives, and managers who want to understand AI without needing a programming background.'],
    ['question' => 'Do I need to know how to write code?', 'answer' => 'Absolutely not. The focus is on strategic application rather than technical implementation.'],
    ['question' => 'What is the core takeaway of this course?', 'answer' => 'You will gain a comprehensive overview of AI history, core concepts, and its massive impact on modern business strategy.'],
    ['question' => 'Will I receive a certificate of completion?', 'answer' => 'Yes, you will receive an official EverLMS certificate upon finishing the program.'],
    ['question' => 'How can I apply this practically?', 'answer' => 'You will be able to identify and evaluate initial AI integration opportunities within your own enterprise.']
  ],
  27 => [
    ['question' => 'Which AI technologies are covered?', 'answer' => 'We focus on Machine Learning, Deep Learning, and Natural Language Processing (NLP) from a business perspective.'],
    ['question' => 'How do these technologies impact operations?', 'answer' => 'They enable extreme automation, precise forecasting, and highly personalized customer service capabilities.'],
    ['question' => 'Is Generative AI included in the syllabus?', 'answer' => 'Yes, a significant portion is dedicated to the disruptive power of Generative AI tools.'],
    ['question' => 'How do I choose the right tech for my company?', 'answer' => 'We provide comprehensive evaluation frameworks to assess cost, ROI, and technical alignment.'],
    ['question' => 'Are there any software prerequisites?', 'answer' => 'No special software is required. The curriculum focuses on business theory and strategic execution.']
  ],
  28 => [
    ['question' => 'Why is Ethical AI so important?', 'answer' => 'Unregulated AI causes massive bias, security risks, and legal liabilities. Ethical management protects brand reputation.'],
    ['question' => 'How does this course address AI bias?', 'answer' => 'We cover methods to identify bias in training datasets and principles for designing transparent AI systems.'],
    ['question' => 'What legal frameworks are explored?', 'answer' => 'We provide overviews of GDPR and other emerging global data protection standards relevant to AI.'],
    ['question' => 'Does AI completely replace human roles?', 'answer' => 'We focus heavily on human-centric AI design, emphasizing augmentation over absolute replacement.'],
    ['question' => 'Can I build an internal company policy?', 'answer' => 'Yes, the course includes templates and guidelines for drafting your own internal AI code of conduct.']
  ],
  29 => [
    ['question' => 'Where do I begin with an AI strategy?', 'answer' => 'Always start with business goals. We guide you through an audit of your current digital maturity.'],
    ['question' => 'What are the fundamental AI strategies?', 'answer' => 'Strategies range from simple internal automation to developing entirely new proprietary AI products.'],
    ['question' => 'How is project ROI calculated?', 'answer' => 'We provide financial modeling tools specifically tuned for measuring returns on intelligent software investments.'],
    ['question' => 'Are real-world case studies provided?', 'answer' => 'Yes, we dissect the successful AI transformation journeys of both large enterprises and agile SMEs.'],
    ['question' => 'How do I pitch AI investments to the board?', 'answer' => 'The capstone project guides you in creating a risk-managed Pitch Deck to secure stakeholder buy-in.']
  ],
  30 => [
    ['question' => 'Does this cover AI digital marketing?', 'answer' => 'Yes, the syllabus explores modern, AI-powered marketing automation platforms and campaign management.'],
    ['question' => 'How can AI improve customer experience?', 'answer' => 'Through hyper-personalization at scale and intelligent 24/7 conversational agents (chatbots).'],
    ['question' => 'Is this applicable to small businesses?', 'answer' => 'Absolutely. The tools discussed include highly affordable SaaS solutions perfectly suited for small teams.'],
    ['question' => 'Is AI-driven SEO part of the curriculum?', 'answer' => 'Yes, an entire module is dedicated to automated content generation and AI-enhanced search optimization.'],
    ['question' => 'What are the prerequisites for this course?', 'answer' => 'A basic understanding of your companys current marketing and customer service workflows is sufficient.']
  ],
  31 => [
    ['question' => 'How does AI help operations?', 'answer' => 'It drastically reduces human error, cuts processing time by up to 70%, and enables smart routing.'],
    ['question' => 'What is the difference between RPA and AI?', 'answer' => 'RPA automates rigid rules, while AI provides cognitive understanding. We teach how to merge both.'],
    ['question' => 'Is this useful for manufacturing?', 'answer' => 'Highly useful. We cover AI applications in supply chain management, inventory forecasting, and predictive maintenance.'],
    ['question' => 'Are automation systems expensive to deploy?', 'answer' => 'Not necessarily. We outline integration tiers, starting from low-cost APIs to enterprise-grade solutions.'],
    ['question' => 'How long does this course take?', 'answer' => 'The standard pace requires about 4-6 hours to complete, yielding immediately actionable insights.']
  ],
  32 => [
    ['question' => 'What is AI Knowledge Management?', 'answer' => 'It relies on AI to index, organize, and rapidly retrieve internal company documents and intranet data.'],
    ['question' => 'Does this cover team collaboration tools?', 'answer' => 'Yes, we analyze AI features within platforms like Slack, Microsoft Teams, and Notion.'],
    ['question' => 'How secure is internal data with AI?', 'answer' => 'We extensively cover Private AI Models and data-sharing constraints to guarantee confidentiality.'],
    ['question' => 'Can I build an internal AI Wiki afterwards?', 'answer' => 'You will be equipped with the architectural knowledge to select vendors and build your own corporate knowledge base.'],
    ['question' => 'Will this knowledge become outdated?', 'answer' => 'No. Knowledge management philosophy is permanent; only the underlying software tools evolve.']
  ],
  33 => [
    ['question' => 'Is SQL knowledge required?', 'answer' => 'Not at all. The focus is on using natural language AI tools to process data without writing code.'],
    ['question' => 'How does BI differ from AI Analytics?', 'answer' => 'Traditional BI reports on the past. AI Analytics emphasizes Predictive Analytics—forecasting what comes next.'],
    ['question' => 'Are PowerBI or Tableau covered?', 'answer' => 'We provide an overview of both, focusing deeply on their recently integrated AI copilot features.'],
    ['question' => 'Can I analyze my own sales data?', 'answer' => 'Yes, internal sales data analysis is a primary use case threaded throughout the curriculum.'],
    ['question' => 'Do I need my own data to practice?', 'answer' => 'No, we provide rich dummy datasets (thousands of rows) for risk-free, hands-on learning.']
  ],
  34 => [
    ['question' => 'Is this for IT pros or Management?', 'answer' => 'The curriculum is tailored to give executives and decision-makers a high-level view of cyber risk.'],
    ['question' => 'How does AI prevent cyber attacks?', 'answer' => 'Through real-time anomaly detection and automated behavioral pattern analysis of potential hackers.'],
    ['question' => 'What types of security are covered?', 'answer' => 'We explore Cloud Security, Network Defense, and Endpoint Protection strategies within an AI context.'],
    ['question' => 'Do hackers use AI too?', 'answer' => 'Yes. An entire chapter is designated to understanding AI-Driven Attacks so you can prepare defenses.'],
    ['question' => 'Does this align with ISO security standards?', 'answer' => 'It implicitly applies the risk-management philosophy central to international standards like ISO 27001.']
  ],
  35 => [
    ['question' => 'What is meant by Data Fundamentals?', 'answer' => 'It refers to the foundational data architecture required to train Large Language Models (LLMs). Bad data equals useless AI.'],
    ['question' => 'Does this involve complex math?', 'answer' => 'No, the course is purely focused on architectural systems thinking and data governance protocols.'],
    ['question' => 'Data Lake vs. Data Warehouse?', 'answer' => 'We break down the pros and cons of both, helping you decide which fits your AI model roadmap.'],
    ['question' => 'Is purchasing third-party data covered?', 'answer' => 'Yes, evaluating and vetting external data vendors is a critical skill taught in the course.'],
    ['question' => 'Are visual learning aids provided?', 'answer' => 'Absolutely. We utilize highly intuitive data architecture diagrams to simplify complex backend concepts.']
  ],
  36 => [
    ['question' => 'How long does data prep take in AI projects?', 'answer' => 'It typically consumes 70-80% of project time. This course teaches you how to optimize that phase.'],
    ['question' => 'Is Data Cleaning covered?', 'answer' => 'Data Cleaning is fundamentally the core of this course, including handling missing values and noise reduction.'],
    ['question' => 'Is the curriculum theoretical or practical?', 'answer' => 'It\'s a balanced 50/50 approach, providing theoretical methodology alongside actionable data classification templates.'],
    ['question' => 'Will we work with image or text data?', 'answer' => 'The primary focus is on Tabular and Text data, which drive most traditional enterprise transformations.'],
    ['question' => 'Are there automated AI cleaning tools?', 'answer' => 'Yes, we will explore specialized cloud-based engines that automate data sanitization workflows.']
  ],
  37 => [
    ['question' => 'Where does Data Culture begin?', 'answer' => 'It must start at the absolute top executive level. We teach strategies to cascade this culture downward.'],
    ['question' => 'How do we handle employee resistance?', 'answer' => 'We utilize Change Management exercises to shift teams away from emotion-based reporting to data-backed decisions.'],
    ['question' => 'How is a successful data culture measured?', 'answer' => 'Through increased productivity and the readiness of staff to query hard metrics during standard meetings.'],
    ['question' => 'How should internal training proceed?', 'answer' => 'Leadership will be provided with sample training workflows in the practical application module.'],
    ['question' => 'Why does culture impact AI success?', 'answer' => 'Because if employees input flawed data, the AI model will forecast entirely incorrect conclusions (Garbage In, Garbage Out).']
  ],
  38 => [
    ['question' => 'Do I need to learn AWS, Google Cloud, or Azure?', 'answer' => 'We will provide high-level overviews of all three major platforms and their respective native AI APIs.'],
    ['question' => 'Is Cloud AI expensive for a micro-business?', 'answer' => 'No. You will learn the Pay-As-You-Go model, allowing you to tightly control costs down to the penny.'],
    ['question' => 'Will I learn how to deploy AI models?', 'answer' => 'You will learn the conceptual deployment roadmap, but DevOps command-line coding is not required.'],
    ['question' => 'Should I build an On-Premise server instead?', 'answer' => 'We provide a detailed On-Premise vs. Cloud comparison so you can definitively answer that question.'],
    ['question' => 'Is data safe on public clouds?', 'answer' => 'Standardized IAM governance and strict AI project permissions fully mitigate those common security fears.']
  ],
  39 => [
    ['question' => 'How does this help with the thousands of tools out there?', 'answer' => 'It provides a "Radar" framework to help you swiftly filter out generic wrappers and identify truly robust software.'],
    ['question' => 'Will this course become outdated as tools change?', 'answer' => 'The evaluation philosophy is evergreen. We teach you *how* to choose, rather than just *what* to choose right now.'],
    ['question' => 'Is a software purchasing checklist provided?', 'answer' => 'Yes, you will receive a comprehensive Vendor Evaluation Form to use during enterprise software procurement.'],
    ['question' => 'Open-Source AI vs. Paid SaaS?', 'answer' => 'It depends on your business. A dedicated lesson covers the strategic "Make vs. Buy" assessment.'],
    ['question' => 'How do I ensure a tool doesn\'t steal my data?', 'answer' => 'We teach you exactly how to skim Terms of Service (TOS) efficiently regarding AI data training clauses.']
  ],
  40 => [
    ['question' => 'Which industries are featured in the case studies?', 'answer' => 'A diverse range including Retail, Healthcare, Real Estate, Financial Services, and Heavy Manufacturing.'],
    ['question' => 'Are there regional specific examples?', 'answer' => 'Beyond Silicon Valley Big Tech, we include targeted analyses of digital transformation in agile SMEs.'],
    ['question' => 'What can we learn from failed projects?', 'answer' => 'We dissect both massive successes and costly failures so you can internalize vital, expensive lessons for free.'],
    ['question' => 'Are there interactive simulations?', 'answer' => 'Yes, practical scenario exercises allow you to outline an AI strategy for a fictional enterprise.'],
    ['question' => 'Are the analysis documents long and difficult?', 'answer' => 'No, all case studies have been distilled into visually rich, highly scannable infographics and charts.']
  ],
  41 => [
    ['question' => 'What exactly is Change Management?', 'answer' => 'It encompasses the psychology and strategic communication required to calm workforce anxiety regarding AI automation.'],
    ['question' => 'Why do AI projects usually fail?', 'answer' => 'Over 70% of failures stem from cultural resistance, not technology. This is why this module is mandatory.'],
    ['question' => 'How do we encourage natural AI adoption?', 'answer' => 'By developing embedded Change Agents—internal champions who organically support colleagues in using daily AI tools.'],
    ['question' => 'How should leadership communicate the change?', 'answer' => 'We provide an internal communication framework designed to align every department under a unified, unthreatening vision.'],
    ['question' => 'Should AI be deployed company-wide instantly?', 'answer' => 'Usually no. The concept of the "Pilot Project" (isolated trial runs) is heavily emphasized as the optimal approach.']
  ],
  42 => [
    ['question' => 'What is the difference between Reskilling and Upskilling?', 'answer' => 'Upskilling improves current abilities. Reskilling teaches entirely new competencies when AI phases out older jobs.'],
    ['question' => 'Is this course essential for HR professionals?', 'answer' => 'Yes, this is an indispensable modernization playbook for any Human Resources Director entering the AI era.'],
    ['question' => 'What if expedited training is too expensive?', 'answer' => 'We teach methods to leverage AI itself to train your workforce via cost-effective Microlearning.'],
    ['question' => 'Which skills are prioritized for the next 3 years?', 'answer' => 'Prompt Engineering, sophisticated Critical Thinking, and Risk Management of automated systems.'],
    ['question' => 'How is employee competence evaluated post-training?', 'answer' => 'It is tangibly measured by KPI growth and the measurable reduction of manual, legacy workflows.']
  ],
  43 => [
    ['question' => 'What is a Center of Excellence (CoE)?', 'answer' => 'It is a cross-functional strike team comprised of elite internal talent that dictates AI strategy company-wide.'],
    ['question' => 'Who belongs on the CoE committee?', 'answer' => 'Ideally, it includes representation from Data Science, Systems Analysis, Legal Counsel, and Executive IT Leadership.'],
    ['question' => 'Does a micro-business need a CoE?', 'answer' => 'Yes, you can deploy a "Lean CoE" model consisting of just 2-3 core consultants advising the broader team.'],
    ['question' => 'What are the main KPIs for a CoE?', 'answer' => 'The volume of profitable AI initiatives launched, sustainable Cloud integration rates, and internal workflow velocity.'],
    ['question' => 'Is building this team prohibitively expensive?', 'answer' => 'The course demonstrates how to blend external vendor expertise with internal staff to dramatically lower overhead costs.']
  ],
  44 => [
    ['question' => 'Why study the Future of AI now?', 'answer' => 'To engage in Future Proofing—ensuring you aren\'t rendered obsolete by aggressive, forward-thinking competitors.'],
    ['question' => 'Is AGI (Artificial General Intelligence) discussed?', 'answer' => 'Yes, the imminent rise of AGI and Agentic Workflows will fundamentally overhaul the concept of corporate operations.'],
    ['question' => 'Will traditional business models die?', 'answer' => 'They will not die, but they will aggressively evolve. This course outlines the 5-to-10-year evolutionary cycle.'],
    ['question' => 'Could there be another "AI Winter"?', 'answer' => 'We objectively evaluate the potential hurdles of resource taxation and tech bubble corrections.'],
    ['question' => 'What role does Virtual Reality AI play?', 'answer' => 'It will become the epicenter of next-generation hyper-interactive M2C (Machine-to-Consumer) business experiences.']
  ],
  45 => [
    ['question' => 'What are the hottest Emerging AI Trends?', 'answer' => 'Edge AI (localized computation without constant internet) and Large Action Models (LAMs) that autonomously execute tasks.'],
    ['question' => 'Is Quantum Machine Learning covered?', 'answer' => 'The course provides an extremely accessible, baseline summary of Quantum computing specifically tailored for administrators.'],
    ['question' => 'Can startups survive alongside Google/OpenAI?', 'answer' => 'Absolutely. The massive trend toward hyper-specialized Small Language Models (SLMs) is the golden era for startups.'],
    ['question' => 'How do I stay updated daily?', 'answer' => 'The curriculum includes a curated directory of elite newsletters (like HuggingFace and ArXiv) for continuous self-education.'],
    ['question' => 'Is the intersection of Web3 and AI analyzed?', 'answer' => 'Yes, a module details how Blockchain ensures the immutability of training data, effectively combatting DeepFake fraud.']
  ],
  46 => [
    ['question' => 'What defines the "Next Wave" of AI?', 'answer' => 'It is the transition into deep logical reasoning and autonomous, multi-tasking intelligent systems (Agents).'],
    ['question' => 'How does a company design a long-term roadmap?', 'answer' => 'By securing Pristine Data, fortifying legal compliance, and transitioning infrastructure to highly agile Hybrid Clouds.'],
    ['question' => 'How do Smart Contracts impact legal teams?', 'answer' => 'They form the basis for Agents negotiating contracts autonomously. You will grasp the fundamentals of Smart Legal AI.'],
    ['question' => 'What about upcoming regulations in the EU/US?', 'answer' => 'The EU AI Act will establish global gold standards. Your enterprise must understand it now to ensure preemptive compliance.'],
    ['question' => 'What is the ultimate lesson of this course?', 'answer' => 'To abandon rigid, reactionary thinking in favor of building open, modular architectures that effortlessly absorb new tech.']
  ],
  47 => [
    ['question' => 'What entails the Final AI Roadmap?', 'answer' => 'It is your capstone project: You will architect a comprehensive, strategic AI execution plan for your actual company.'],
    ['question' => 'How long should this Roadmap document be?', 'answer' => 'The course provides a condensed, 30-slide strategic template optimized for immediate Board of Directors presentations.'],
    ['question' => 'Does AI assist in reviewing my project?', 'answer' => 'Yes, the AI engine integrated within EverLMS will provide direct, real-time feedback on your strategic thought process.'],
    ['question' => 'Is financial resource planning included?', 'answer' => 'Yes, a simulated but highly realistic budget model—tracking current market API costs—is provided for accuracy.'],
    ['question' => 'What title do I earn upon completion?', 'answer' => 'You will be awarded the "AI Business Strategist Master" certificate, proving your capacity to lead elite digital transformations.']
  ]
];

$curriculum = json_decode(file_get_contents($json_path), TRUE);

// 1. UPDATE JSON ARRAY
$json_updated = false;
foreach ($curriculum as &$cat) {
  if (empty($cat['courses'])) continue;
  foreach ($cat['courses'] as &$course) {
    if (!empty($course['id'])) {
      $cid = $course['id'];
      if (isset($faqs_map[$cid])) {
        $course['faq'] = $faqs_map[$cid];
        $course['is_faq_generated'] = true;
        $json_updated = true;
      }
    }
  }
}

if ($json_updated) {
  file_put_contents($json_path, json_encode($curriculum, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  echo "=> Cập nhật file ai_curriculum.json sang bộ FAQ Tiếng Anh... Thành công!\n";
}

// 2. OVERWRITE LMS_FAQ ENTITIES IN DATABASE
$faq_storage = \Drupal::entityTypeManager()->getStorage('lms_faq');
$db_updated_count = 0;

foreach ($faqs_map as $course_id => $faq_list) {
  // Kiểm tra xem Course này có thực sự tồn tại trong Drupal DB ko
  $course_entity = \Drupal::entityTypeManager()->getStorage('lms_course')->load($course_id);
  if (!$course_entity) {
    continue;
  }

  // Xóa các FAQ cũ đang gán cho khóa học này
  $existing_faqs = $faq_storage->getQuery()
    ->condition('field_course', $course_id)
    ->accessCheck(FALSE)
    ->execute();
  if (!empty($existing_faqs)) {
      $old_entities = $faq_storage->loadMultiple($existing_faqs);
      $faq_storage->delete($old_entities);
  }

  // Tạo Entity FAQ mới
  $weight = 0;
  foreach ($faq_list as $faq_item) {
    $faq_entity = $faq_storage->create([
      'question' => $faq_item['question'],
      'field_answer' => [
        'value' => '<p>' . $faq_item['answer'] . '</p>',
        'format' => 'full_html',
      ],
      'field_scope' => 'course',
      'field_course' => [
        'target_id' => $course_id,
      ],
      'weight' => $weight++,
      'status' => 1,
    ]);
    $faq_entity->save();
    $db_updated_count++;
  }
}

echo "=> Xóa FAQ tiếng Việt cũ và Import tổng cộng $db_updated_count câu FAQ tiếng Anh mới vào Database... Hoàn tất!\n\n";
echo "Bây giờ bạn lên giao diện Tab FAQ của khóa học sẽ thấy ngay nhé!\n";
