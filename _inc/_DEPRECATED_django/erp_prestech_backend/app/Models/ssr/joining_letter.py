import os
from .markup_template import MarkupTemplate
from django.conf import settings

class JoiningLetter(MarkupTemplate):

  class Meta:
    db_table = "joining_letters"
    verbose_name = "Joining Letter"
    verbose_name_plural = "Joining Letters"

  def __str__(self) -> str:
    return f"{self.lang}: {self.content[:50]}"

  @staticmethod
  def replace_variable(content: str, obj: dict) -> str:
    arr_variable = [
      "{date}",
      "{app_name}",
      "{employee_name}",
      "{address}",
      "{start_date}",
      "{designation}",
      "{branch}",
      "{start_time}",
      "{end_time}",
      "{total_hours}",
    ]
    arr_value = {
      "date": "-",
      "app_name": "-",
      "employee_name": "-",
      "address": "-",
      "start_date": "-",
      "designation": "-",
      "branch": "-",
      "start_time": "-",
      "end_time": "-",
      "total_hours": "-",
    }
    if obj:
      for key, val in obj.items():
        arr_value[key] = val
    arr_value["app_name"] = getattr(settings, "APP_NAME", os.environ.get("APP_NAME", "-"))
    for variable in arr_variable:
      key = variable.strip("{}")
      content = content.replace(variable, arr_value.get(key, "-"))
    return content
  
  @staticmethod
  def default_joining_letter():
        """
        Creates default joining letter templates.
        Supply your default template mapping language codes to contents.
        """
        default_template = {
					'ar': '''<h2 style="text-align: center;"><strong>خطاب الانضمام</strong></h2>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>الموضوع: موعد لوظيفة {designation}</p>
							<p>عزيزي {employee_name} ،</p>
							<p>يسعدنا أن نقدم لك منصب {designation} مع {app_name} "الشركة" وفقًا للشروط التالية و</p>
							<p>الظروف:</p>
							<p>1. بدء العمل</p>
							<p>سيصبح عملك ساريًا اعتبارًا من {start_date}</p>
							<p>2. المسمى الوظيفي</p>
							<p>سيكون المسمى الوظيفي الخاص بك هو {designation}.</p>
							<p>3. الراتب</p>
							<p>سيكون راتبك والمزايا الأخرى على النحو المبين في الجدول 1 ، طيه.</p>
							<p>4. مكان الإرسال</p>
							<p>سيتم إرسالك إلى {branch}. ومع ذلك ، قد يُطلب منك العمل في أي مكان عمل تمتلكه الشركة ، أو</p>
							<p>قد تحصل لاحقًا.</p>
							<p>5. ساعات العمل</p>
							<p>أيام العمل العادية هي من الاثنين إلى الجمعة. سيُطلب منك العمل لساعات حسب الضرورة لـ</p>
							<p>أداء واجباتك على النحو الصحيح تجاه الشركة. ساعات العمل العادية من {start_time} إلى {end_time} وأنت</p>
							<p>من المتوقع أن يعمل ما لا يقل عن {total_hours} ساعة كل أسبوع ، وإذا لزم الأمر لساعات إضافية اعتمادًا على</p>
							<p>المسؤوليات.</p>
							<p>6. الإجازة / العطل</p>
							<p>6.1 يحق لك الحصول على إجازة غير رسمية مدتها 12 يومًا.</p>
							<p>6.2 يحق لك الحصول على إجازة مرضية مدفوعة الأجر لمدة 12 يوم عمل.</p>
							<p>6.3 تخطر الشركة بقائمة الإجازات المعلنة في بداية كل عام.</p>
							<p>7. طبيعة الواجبات</p>
							<p>ستقوم بأداء أفضل ما لديك من واجبات متأصلة في منصبك ومهام إضافية مثل الشركة</p>
							<p>قد يدعوك لأداء ، من وقت لآخر. واجباتك المحددة منصوص عليها في الجدول الثاني بهذه الرسالة.</p>
							<p>8. ممتلكات الشركة</p>
							<p>ستحافظ دائمًا على ممتلكات الشركة في حالة جيدة ، والتي قد يتم تكليفك بها للاستخدام الرسمي خلال فترة عملها</p>
							<p>عملك ، ويجب أن تعيد جميع هذه الممتلكات إلى الشركة قبل التخلي عن الرسوم الخاصة بك ، وإلا فإن التكلفة</p>
							<p>نفس الشيء سوف تسترده منك الشركة.</p>
							<p>9. الاقتراض / قبول الهدايا</p>
							<p>لن تقترض أو تقبل أي أموال أو هدية أو مكافأة أو تعويض مقابل مكاسبك الشخصية من أو تضع نفسك بأي طريقة أخرى</p>
							<p>بموجب التزام مالي تجاه أي شخص / عميل قد تكون لديك تعاملات رسمية معه.</p>
							<p>10. الإنهاء</p>
							<p>10.1 يمكن للشركة إنهاء موعدك ، دون أي سبب ، من خلال إعطائك ما لا يقل عن [إشعار] قبل أشهر</p>
							<p>إشعار خطي أو راتب بدلاً منه. لغرض هذا البند ، يقصد بالراتب المرتب الأساسي.</p>
							<p>10.2 إنهاء عملك مع الشركة ، دون أي سبب ، من خلال تقديم ما لا يقل عن إشعار الموظف</p>
							<p>أشهر الإخطار أو الراتب عن الفترة غير المحفوظة ، المتبقية بعد تعديل الإجازات المعلقة ، كما في التاريخ.</p>
							<p>10.3 تحتفظ الشركة بالحق في إنهاء عملك بإيجاز دون أي فترة إشعار أو مدفوعات إنهاء</p>
							<p>إذا كان لديه سبب معقول للاعتقاد بأنك مذنب بسوء السلوك أو الإهمال ، أو ارتكبت أي خرق جوهري لـ</p>
							<p>العقد ، أو تسبب في أي خسارة للشركة.</p>
							<p>10. 4 عند إنهاء عملك لأي سبب من الأسباب ، ستعيد إلى الشركة جميع ممتلكاتك ؛ المستندات و</p>
							<p>الأوراق الأصلية ونسخها ، بما في ذلك أي عينات ، وأدبيات ، وعقود ، وسجلات ، وقوائم ، ورسومات ، ومخططات ،</p>
							<p>الرسائل والملاحظات والبيانات وما شابه ذلك ؛ والمعلومات السرية التي بحوزتك أو تحت سيطرتك والمتعلقة بك</p>
							<p>التوظيف أو الشؤون التجارية للعملاء.</p>
							<p>11. المعلومات السرية</p>
							<p>11. 1 أثناء عملك في الشركة ، سوف تكرس وقتك واهتمامك ومهارتك كلها بأفضل ما لديك من قدرات</p>
							<p>عملها. لا يجوز لك ، بشكل مباشر أو غير مباشر ، الانخراط أو الارتباط بنفسك ، أو الارتباط به ، أو القلق ، أو التوظيف ، أو</p>
							<p>الوقت أو متابعة أي دورة دراسية على الإطلاق ، دون الحصول على إذن مسبق من الشركة أو الانخراط في أي عمل آخر أو</p>
							<p>الأنشطة أو أي وظيفة أخرى أو العمل بدوام جزئي أو متابعة أي دورة دراسية على الإطلاق ، دون إذن مسبق من</p>
							<p>شركة.</p>
							<p>11. المعلومات السرية</p>
							<p>11. 1 أثناء عملك في الشركة ، سوف تكرس وقتك واهتمامك ومهارتك كلها بأفضل ما لديك من قدرات</p>
							<p>عملها. لا يجوز لك ، بشكل مباشر أو غير مباشر ، الانخراط أو الارتباط بنفسك ، أو الارتباط به ، أو القلق ، أو التوظيف ، أو</p>
							<p>الوقت أو متابعة أي دورة دراسية على الإطلاق ، دون الحصول على إذن مسبق من الشركة أو الانخراط في أي عمل آخر أو</p>
							<p>الأنشطة أو أي وظيفة أخرى أو العمل بدوام جزئي أو متابعة أي دورة دراسية على الإطلاق ، دون إذن مسبق من</p>
							<p>شركة.</p>
							<p>11.2 يجب عليك دائمًا الحفاظ على أعلى درجة من السرية والحفاظ على سرية السجلات والوثائق وغيرها</p>
							<p>المعلومات السرية المتعلقة بأعمال الشركة والتي قد تكون معروفة لك أو مخولة لك بأي وسيلة</p>
							<p>ولن تستخدم هذه السجلات والمستندات والمعلومات إلا بالطريقة المصرح بها حسب الأصول لصالح الشركة. إلى عن على</p>
							<p>أغراض هذا البند "المعلومات السرية" تعني المعلومات المتعلقة بأعمال الشركة وعملائها</p>
							<p>التي لا تتوفر لعامة الناس والتي قد تتعلمها أثناء عملك. هذا يشمل،</p>
							<p>على سبيل المثال لا الحصر ، المعلومات المتعلقة بالمنظمة وقوائم العملاء وسياسات التوظيف والموظفين والمعلومات</p>
							<p>حول منتجات الشركة وعملياتها بما في ذلك الأفكار والمفاهيم والإسقاطات والتكنولوجيا والكتيبات والرسم والتصاميم ،</p>
							<p>المواصفات وجميع الأوراق والسير الذاتية والسجلات والمستندات الأخرى التي تحتوي على هذه المعلومات السرية.</p>
							<p>11.3 لن تقوم في أي وقت بإزالة أي معلومات سرية من المكتب دون إذن.</p>
							<p>11.4 واجبك في الحماية وعدم الإفشاء</p>
							<p>تظل المعلومات السرية سارية بعد انتهاء أو إنهاء هذه الاتفاقية و / أو عملك مع الشركة.</p>
							<p>11.5 سوف يجعلك خرق شروط هذا البند عرضة للفصل بإجراءات موجزة بموجب الفقرة أعلاه بالإضافة إلى أي</p>
							<p>أي تعويض آخر قد يكون للشركة ضدك في القانون.</p>
							<p>12. الإخطارات</p>
							<p>يجوز لك إرسال إخطارات إلى الشركة على عنوان مكتبها المسجل. يمكن أن ترسل لك الشركة إشعارات على</p>
							<p>العنوان الذي أشرت إليه في السجلات الرسمية.</p>
							<p>13. تطبيق سياسة الشركة</p>
							<p>يحق للشركة تقديم إعلانات السياسة من وقت لآخر فيما يتعلق بمسائل مثل استحقاق الإجازة والأمومة</p>
							<p>الإجازة ، ومزايا الموظفين ، وساعات العمل ، وسياسات النقل ، وما إلى ذلك ، ويمكن تغييرها من وقت لآخر وفقًا لتقديرها الخاص.</p>
							<p>جميع قرارات سياسة الشركة هذه ملزمة لك ويجب أن تلغي هذه الاتفاقية إلى هذا الحد.</p>
							<p>14. القانون الحاكم / الاختصاص القضائي</p>
							<p>يخضع عملك في الشركة لقوانين الدولة. تخضع جميع النزاعات للاختصاص القضائي للمحكمة العليا</p>
							<p>غوجارات فقط.</p>
							<p>15. قبول عرضنا</p>
							<p>يرجى تأكيد قبولك لعقد العمل هذا من خلال التوقيع وإعادة النسخة المكررة.</p>
							<p>نرحب بكم ونتطلع إلى تلقي موافقتكم والعمل معكم.</p>
							<p>تفضلوا بقبول فائق الاحترام،</p>
							<p>{app_name}</p>
							<p>{date}</p>''',
					'zh': '''<h3 style="text-align: center;">加入信</h3>
								<p>{日期}</p>
								<p>{employee_name}</p>
								<p>{地址}</p>
								<p>主题：任命 {designation} 职位</p>
								<p>亲爱的{employee_name}，</p>
								<p>我们很高兴根据以下条款向您提供 {app_name} theCompany 的 {designation} 职位，并且</p>
								<p>条件：</p>
								<p>1.开始就业</p>
								<p>您的雇佣关系将于 {start_date}起生效</p>
								<p>2.职位名称</p>
								<p>您的职位名称为{designation}。</p>
								<p>3.薪资</p>
								<p>您的工资和其他福利将在附表 1 中列出。</p>
								<p>4.发帖地点</p>
								<p>您将被调往{branch}。但是，您可能需要在公司拥有的任何营业地点工作，或者</p>
								<p>稍后可能会获得。</p>
								<p>5.工作时间</p>
								<p>正常工作日为周一至周五。您将需要在必要的时间内工作</p>
								<p>正确履行您对公司的职责。正常工作时间为 {start_time} 至 {end_time}，您</p>
								<p>预计每周工作不少于 {total_hours} 小时，如有必要，可根据您的情况增加工作时间</p>
								<p>职责。</p>
								<p>6.休假/节假日</p>
								<p>6.1 您有权享受 12 天的事假。</p>
								<p>6.2 您有权享受 12 个工作日的带薪病假。</p>
								<p>6.3 公司应在每年年初公布已宣布的假期清单。</p>''',
					'da': '''<h3 style="text-align: center;"><strong>Tilslutningsbrev</strong></h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>Emne: Udn&aelig;vnelse til stillingen som {designation}</p>
							<p>K&aelig;re {employee_name}</p>
							<p>Vi er glade for at kunne tilbyde dig stillingen som {designation} hos {app_name} "Virksomheden" p&aring; f&oslash;lgende vilk&aring;r og</p>
							<p>betingelser:</p>
							<p>1. P&aring;begyndelse af ans&aelig;ttelse</p>
							<p>Din ans&aelig;ttelse tr&aelig;der i kraft fra {start_date}</p>
							<p>2. Jobtitel</p>
							<p>Din jobtitel vil v&aelig;re {designation}.</p>
							<p>3. L&oslash;n</p>
							<p>Din l&oslash;n og andre goder vil v&aelig;re som angivet i skema 1, hertil.</p>
							<p>4. Udstationeringssted</p>
							<p>Du vil blive sl&aring;et op p&aring; {branch}. Du kan dog blive bedt om at arbejde p&aring; ethvert forretningssted, som virksomheden har, eller</p>
							<p>senere kan erhverve.</p>
							<p>5. Arbejdstimer</p>
							<p>De normale arbejdsdage er mandag til fredag. Du vil blive forpligtet til at arbejde i de timer, som er n&oslash;dvendige for</p>
							<p>beh&oslash;rig varetagelse af dine pligter over for virksomheden. Den normale arbejdstid er fra {start_time} til {end_time}, og det er du</p>
							<p>forventes at arbejde ikke mindre end {total_hours} timer hver uge, og om n&oslash;dvendigt yderligere timer afh&aelig;ngigt af din</p>
							<p>ansvar.</p>''',
					'de': '''<h3 style="text-align: center;"><strong>Beitrittsbrief</strong></h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>Betreff: Ernennung f&uuml;r die Stelle von {designation}</p>
							<p>Sehr geehrter {employee_name},</p>
							<p>Wir freuen uns, Ihnen die Position von {designation} bei {app_name} dem &bdquo;Unternehmen&ldquo; zu den folgenden Bedingungen anbieten zu k&ouml;nnen</p>
							<p>Bedingungen:</p>
							<p>1. Aufnahme des Arbeitsverh&auml;ltnisses</p>
							<p>Ihre Anstellung gilt ab dem {start_date}</p>
							<p>2. Berufsbezeichnung</p>
							<p>Ihre Berufsbezeichnung lautet {designation}.</p>
							<p>3. Gehalt</p>
							<p>Ihr Gehalt und andere Leistungen sind in Anhang 1 zu diesem Dokument aufgef&uuml;hrt.</p>
							<p>4. Postort</p>
							<p>Sie werden bei {branch} eingestellt. Es kann jedoch erforderlich sein, dass Sie an jedem Gesch&auml;ftssitz arbeiten, den das Unternehmen hat, oder</p>
							<p>sp&auml;ter erwerben kann.</p>
							<p>5. Arbeitszeit</p>
							<p>Die normalen Arbeitstage sind Montag bis Freitag. Sie m&uuml;ssen so viele Stunden arbeiten, wie es f&uuml;r die erforderliche</p>
							<p>ordnungsgem&auml;&szlig;e Erf&uuml;llung Ihrer Pflichten gegen&uuml;ber dem Unternehmen notwendig sind. Die normalen Arbeitszeiten sind von {start_time} bis {end_time} und Sie werden</p>
							<p>voraussichtlich nicht weniger als {total_hours} Stunden pro Woche arbeiten, und falls erforderlich, auch zus&auml;tzliche Stunden leisten.</p>''',
					'en': '''<h3 style="text-align: center;">Joining Letter</h3>
								<p>{date}</p>
								<p>{employee_name}</p>
								<p>{address}</p>
								<p>Subject: Appointment for the post of {designation}</p>
								<p>Dear {employee_name},</p>
								<p>We are pleased to offer you the position of {designation} with {app_name} theCompany on the following terms and</p>
								<p>conditions:</p>
								<p>1. Commencement of employment</p>
								<p>Your employment will be effective, as of {start_date}</p>
								<p>2. Job title</p>
								<p>Your job title will be{designation}.</p>
								<p>3. Salary</p>
								<p>Your salary and other benefits will be as set out in Schedule 1, hereto.</p>
								<p>4. Place of posting</p>
								<p>You will be posted at {branch}. You may however be required to work at any place of business which the Company has, or</p>
								<p>may later acquire.</p>
								<p>5. Hours of Work</p>
								<p>The normal working days are Monday through Friday. You will be required to work for such hours as necessary for the</p>
								<p>proper discharge of your duties to the Company. The normal working hours are from {start_time} to {end_time} and you are</p>
								<p>expected to work not less than {total_hours} hours each week, and if necessary for additional hours depending on your</p>
								<p>responsibilities.</p>
								<p>6. Leave/Holidays</p>
								<p>6.1 You are entitled to casual leave of 12 days.</p>
								<p>6.2 You are entitled to 12 working days of paid sick leave.</p>
								<p>6.3 The Company shall notify a list of declared holidays at the beginning of each year.</p>
								<p>7. Nature of duties</p>
								<p>You will perform to the best of your ability all the duties as are inherent in your post and such additional duties as the company</p>
								<p>may call upon you to perform, from time to time. Your specific duties are set out in Schedule II hereto.</p>
								<p>8. Company property</p>
								<p>You will always maintain in good condition Company property, which may be entrusted to you for official use during the course of</p>
								<p>your employment, and shall return all such property to the Company prior to relinquishment of your charge, failing which the cost</p>
								<p>of the same will be recovered from you by the Company.</p>
								<p>9. Borrowing/accepting gifts</p>
								<p>You will not borrow or accept any money, gift, reward, or compensation for your personal gains from or otherwise place yourself</p>
								<p>under pecuniary obligation to any person/client with whom you may be having official dealings.</p>
								<p>10. Termination</p>
								<p>10.1 Your appointment can be terminated by the Company, without any reason, by giving you not less than [Notice] months prior</p>
								<p>notice in writing or salary in lieu thereof. For the purpose of this clause, salary shall mean basic salary.</p>
								<p>10.2 You may terminate your employment with the Company, without any cause, by giving no less than [Employee Notice]</p>
								<p>months prior notice or salary for the unsaved period, left after adjustment of pending leaves, as on date.</p>
								<p>10.3 The Company reserves the right to terminate your employment summarily without any notice period or termination payment</p>
								<p>if it has reasonable ground to believe you are guilty of misconduct or negligence, or have committed any fundamental breach of</p>
								<p>contract, or caused any loss to the Company.</p>
								<p>10. 4 On the termination of your employment for whatever reason, you will return to the Company all property; documents, and</p>
								<p>paper, both original and copies thereof, including any samples, literature, contracts, records, lists, drawings, blueprints,</p>
								<p>letters, notes, data and the like; and Confidential Information, in your possession or under your control relating to your</p>
								<p>employment or to clients business affairs.</p>
								<p>11. Confidential Information</p>
								<p>11. 1 During your employment with the Company you will devote your whole time, attention, and skill to the best of your ability for</p>
								<p>its business. You shall not, directly or indirectly, engage or associate yourself with, be connected with, concerned, employed, or</p>
								<p>time or pursue any course of study whatsoever, without the prior permission of the Company.engaged in any other business or</p>
								<p>activities or any other post or work part-time or pursue any course of study whatsoever, without the prior permission of the</p>
								<p>Company.</p>
								<p>11.2 You must always maintain the highest degree of confidentiality and keep as confidential the records, documents, and other</p>
								<p>Confidential Information relating to the business of the Company which may be known to you or confided in you by any means</p>
								<p>and you will use such records, documents and information only in a duly authorized manner in the interest of the Company. For</p>
								<p>the purposes of this clauseConfidential Information means information about the Companys business and that of its customers</p>
								<p>which is not available to the general public and which may be learned by you in the course of your employment. This includes,</p>
								<p>but is not limited to, information relating to the organization, its customer lists, employment policies, personnel, and information</p>
								<p>about the Companys products, processes including ideas, concepts, projections, technology, manuals, drawing, designs,</p>
								<p>specifications, and all papers, resumes, records and other documents containing such Confidential Information.</p>
								<p>11.3 At no time, will you remove any Confidential Information from the office without permission.</p>
								<p>11.4 Your duty to safeguard and not disclos</p>
								<p>e Confidential Information will survive the expiration or termination of this Agreement and/or your employment with the Company.</p>
								<p>11.5 Breach of the conditions of this clause will render you liable to summary dismissal under the clause above in addition to any</p>
								<p>other remedy the Company may have against you in law.</p>
								<p>12. Notices</p>
								<p>Notices may be given by you to the Company at its registered office address. Notices may be given by the Company to you at</p>
								<p>the address intimated by you in the official records.</p>
								<p>13. Applicability of Company Policy</p>
								<p>The Company shall be entitled to make policy declarations from time to time pertaining to matters like leave entitlement,maternity</p>
								<p>leave, employees benefits, working hours, transfer policies, etc., and may alter the same from time to time at its sole discretion.</p>
								<p>All such policy decisions of the Company shall be binding on you and shall override this Agreement to that extent.</p>
								<p>14. Governing Law/Jurisdiction</p>
								<p>Your employment with the Company is subject to Country laws. All disputes shall be subject to the jurisdiction of High Court</p>
								<p>Gujarat only.</p>
								<p>15. Acceptance of our offer</p>
								<p>Please confirm your acceptance of this Contract of Employment by signing and returning the duplicate copy.</p>
								<p>We welcome you and look forward to receiving your acceptance and to working with you.</p>
								<p>Yours Sincerely,</p>
								<p>{app_name}</p>
								<p>{date}</p>''',
					'es': '''<h3 style="text-align: center;"><strong>Carta de uni&oacute;n</strong></h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>Asunto: Nombramiento para el puesto de {designation}</p>
							<p>Estimado {employee_name},</p>
							<p>Nos complace ofrecerle el puesto de {designation} con {app_name}, la Compa&ntilde;&iacute;a en los siguientes t&eacute;rminos y</p>
							<p>condiciones:</p>
							<p>1. Comienzo del empleo</p>
							<p>Su empleo ser&aacute; efectivo a partir del {start_date}</p>
							<p>2. T&iacute;tulo del trabajo</p>
							<p>El t&iacute;tulo de su trabajo ser&aacute; {designation}.</p>
							<p>3. Salario</p>
							<p>Su salario y otros beneficios ser&aacute;n los establecidos en el Anexo 1 del presente.</p>
							<p>4. Lugar de destino</p>
							<p>Se le publicar&aacute; en {branch}. Sin embargo, es posible que deba trabajar en cualquier lugar de negocios que tenga la Compa&ntilde;&iacute;a, o</p>
							<p>puede adquirir posteriormente.</p>
							<p>5. Horas de trabajo</p>
							<p>Los d&iacute;as normales de trabajo son de lunes a viernes. Se le pedir&aacute; que trabaje las horas que sean necesarias para el</p>
							<p>cumplimiento adecuado de sus deberes para con la Compa&ntilde;&iacute;a. El horario normal de trabajo es de {start_time} a {end_time} y usted est&aacute;</p>
							<p>se espera que trabaje no menos de {total_hours} horas cada semana y, si es necesario, horas adicionales dependiendo de su</p>
							<p>responsabilidades.</p>
							<p>6. Licencia/Vacaciones</p>
							<p>6.1 Tiene derecho a un permiso eventual de 12 d&iacute;as.</p>
							<p>6.2 Tiene derecho a 12 d&iacute;as laborables de baja por enfermedad remunerada.</p>
							<p>6.3 La Compa&ntilde;&iacute;a deber&aacute; notificar una lista de d&iacute;as festivos declarados al comienzo de cada a&ntilde;o.</p>
							<p>7. Naturaleza de los deberes</p>
							<p>Desempe&ntilde;ar&aacute; lo mejor que pueda todas las funciones inherentes a su puesto y aquellas funciones adicionales que la empresa</p>
							<p>puede pedirte que act&uacute;es, de vez en cuando. Sus deberes espec&iacute;ficos se establecen en el Anexo II del presente.</p>
							<p>8. Propiedad de la empresa</p>
							<p>Siempre mantendr&aacute; en buenas condiciones la propiedad de la Compa&ntilde;&iacute;a, que se le puede confiar para uso oficial durante el curso de</p>
							<p>su empleo, y devolver&aacute; todos esos bienes a la Compa&ntilde;&iacute;a antes de renunciar a su cargo, en caso contrario, el costo</p>
							<p>de la misma ser&aacute; recuperada de usted por la Compa&ntilde;&iacute;a.</p>
							<p>9. Tomar prestado/aceptar regalos</p>
							<p>No pedir&aacute; prestado ni aceptar&aacute; dinero, obsequios, recompensas o compensaciones por sus ganancias personales o se colocar&aacute; de otra manera</p>
							<p>bajo obligaci&oacute;n pecuniaria a cualquier persona/cliente con quien pueda tener tratos oficiales.</p>
							<p>10. Terminaci&oacute;n</p>
							<p>10.1 Su nombramiento puede ser rescindido por la Compa&ntilde;&iacute;a, sin ning&uacute;n motivo, al darle no menos de [Aviso] meses antes</p>
							<p>aviso por escrito o salario en su lugar. Para los efectos de esta cl&aacute;usula, se entender&aacute; por salario el salario base.</p>
							<p>10.2 Puede rescindir su empleo con la Compa&ntilde;&iacute;a, sin ninguna causa, dando no menos de [Aviso al empleado]</p>
							<p>meses de preaviso o salario por el per&iacute;odo no ahorrado, remanente despu&eacute;s del ajuste de licencias pendientes, a la fecha.</p>
							<p>10.3 La Compa&ntilde;&iacute;a se reserva el derecho de rescindir su empleo sumariamente sin ning&uacute;n per&iacute;odo de preaviso o pago por rescisi&oacute;n</p>
							<p>si tiene motivos razonables para creer que usted es culpable de mala conducta o negligencia, o ha cometido una violaci&oacute;n fundamental de</p>
							<p>contrato, o causado cualquier p&eacute;rdida a la Compa&ntilde;&iacute;a.</p>
							<p>10. 4 A la terminaci&oacute;n de su empleo por cualquier motivo, devolver&aacute; a la Compa&ntilde;&iacute;a todos los bienes; documentos, y</p>
							<p>papel, tanto en original como en copia del mismo, incluyendo cualquier muestra, literatura, contratos, registros, listas, dibujos, planos,</p>
							<p>cartas, notas, datos y similares; e Informaci&oacute;n confidencial, en su posesi&oacute;n o bajo su control en relaci&oacute;n con su</p>
							<p>empleo o a los asuntos comerciales de los clientes.</p>
							<p>11. Informaci&oacute;n confidencial</p>
							<p>11. 1 Durante su empleo en la Compa&ntilde;&iacute;a, dedicar&aacute; todo su tiempo, atenci&oacute;n y habilidad lo mejor que pueda para</p>
							<p>son negocios. Usted no deber&aacute;, directa o indirectamente, comprometerse o asociarse con, estar conectado, interesado, empleado o</p>
							<p>tiempo o seguir cualquier curso de estudio, sin el permiso previo de la Compa&ntilde;&iacute;a. participar en cualquier otro negocio o</p>
							<p>actividades o cualquier otro puesto o trabajo a tiempo parcial o seguir cualquier curso de estudio, sin el permiso previo de la</p>
							<p>Compa&ntilde;&iacute;a.</p>
							<p>11.2 Siempre debe mantener el m&aacute;s alto grado de confidencialidad y mantener como confidenciales los registros, documentos y otros</p>
							<p>Informaci&oacute;n confidencial relacionada con el negocio de la Compa&ntilde;&iacute;a que usted pueda conocer o confiarle por cualquier medio</p>
							<p>y utilizar&aacute; dichos registros, documentos e informaci&oacute;n solo de manera debidamente autorizada en inter&eacute;s de la Compa&ntilde;&iacute;a. Para</p>
							<p>A los efectos de esta cl&aacute;usula, "Informaci&oacute;n confidencial" significa informaci&oacute;n sobre el negocio de la Compa&ntilde;&iacute;a y el de sus clientes.</p>
							<p>que no est&aacute; disponible para el p&uacute;blico en general y que usted puede aprender en el curso de su empleo. Esto incluye,</p>
							<p>pero no se limita a, informaci&oacute;n relacionada con la organizaci&oacute;n, sus listas de clientes, pol&iacute;ticas de empleo, personal e informaci&oacute;n</p>
							<p>sobre los productos de la Compa&ntilde;&iacute;a, procesos que incluyen ideas, conceptos, proyecciones, tecnolog&iacute;a, manuales, dibujos, dise&ntilde;os,</p>
							<p>especificaciones, y todos los papeles, curr&iacute;culos, registros y otros documentos que contengan dicha Informaci&oacute;n Confidencial.</p>
							<p>11.3 En ning&uacute;n momento, sacar&aacute; ninguna Informaci&oacute;n Confidencial de la oficina sin permiso.</p>
							<p>11.4 Su deber de salvaguardar y no divulgar</p>
							<p>La Informaci&oacute;n Confidencial sobrevivir&aacute; a la expiraci&oacute;n o terminaci&oacute;n de este Acuerdo y/o su empleo con la Compa&ntilde;&iacute;a.</p>
							<p>11.5 El incumplimiento de las condiciones de esta cl&aacute;usula le har&aacute; pasible de despido sumario en virtud de la cl&aacute;usula anterior adem&aacute;s de cualquier</p>
							<p>otro recurso que la Compa&ntilde;&iacute;a pueda tener contra usted por ley.</p>
							<p>12. Avisos</p>
							<p>Usted puede enviar notificaciones a la Compa&ntilde;&iacute;a a su domicilio social. La Compa&ntilde;&iacute;a puede enviarle notificaciones a usted en</p>
							<p>la direcci&oacute;n indicada por usted en los registros oficiales.</p>
							<p>13. Aplicabilidad de la pol&iacute;tica de la empresa</p>
							<p>La Compa&ntilde;&iacute;a tendr&aacute; derecho a hacer declaraciones de pol&iacute;tica de vez en cuando relacionadas con asuntos como el derecho a licencia, maternidad</p>
							<p>licencia, beneficios de los empleados, horas de trabajo, pol&iacute;ticas de transferencia, etc., y puede modificarlas de vez en cuando a su sola discreci&oacute;n.</p>
							<p>Todas las decisiones pol&iacute;ticas de la Compa&ntilde;&iacute;a ser&aacute;n vinculantes para usted y anular&aacute;n este Acuerdo en esa medida.</p>
							<p>14. Ley aplicable/Jurisdicci&oacute;n</p>
							<p>Su empleo con la Compa&ntilde;&iacute;a est&aacute; sujeto a las leyes del Pa&iacute;s. Todas las disputas estar&aacute;n sujetas a la jurisdicci&oacute;n del Tribunal Superior</p>
							<p>S&oacute;lo Gujarat.</p>
							<p>15. Aceptaci&oacute;n de nuestra oferta</p>
							<p>Por favor, confirme su aceptaci&oacute;n de este Contrato de Empleo firmando y devolviendo el duplicado.</p>
							<p>Le damos la bienvenida y esperamos recibir su aceptaci&oacute;n y trabajar con usted.</p>
							<p>Tuyo sinceramente,</p>
							<p>{app_name}</p>
							<p>{date}</p>''',
					'fr': '''<h3 style="text-align: center;">Lettre dadh&eacute;sion</h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>Objet : Nomination pour le poste de {designation}</p>
							<p>Cher {employee_name},</p>
							<p>Nous sommes heureux de vous proposer le poste de {designation} avec {app_name} la "Soci&eacute;t&eacute;" selon les conditions suivantes et</p>
							<p>les conditions:</p>
							<p>1. Entr&eacute;e en fonction</p>
							<p>Votre emploi sera effectif &agrave; partir du {start_date}</p>
							<p>2. Intitul&eacute; du poste</p>
							<p>Votre titre de poste sera {designation}.</p>
							<p>3. Salaire</p>
							<p>Votre salaire et vos autres avantages seront tels quindiqu&eacute;s &agrave; lannexe 1 ci-jointe.</p>
							<p>4. Lieu de d&eacute;tachement</p>
							<p>Vous serez affect&eacute; &agrave; {branch}. Vous pouvez cependant &ecirc;tre tenu de travailler dans nimporte quel lieu daffaires que la Soci&eacute;t&eacute; a, ou</p>
							<p>pourra acqu&eacute;rir plus tard.</p>
							<p>5. Heures de travail</p>
							<p>Les jours ouvrables normaux sont du lundi au vendredi. Vous devrez travailler les heures n&eacute;cessaires &agrave; la</p>
							<p>lexercice correct de vos fonctions envers la Soci&eacute;t&eacute;. Les heures normales de travail vont de {start_time} &agrave; {end_time} et vous &ecirc;tes</p>
							<p>devrait travailler au moins {total_hours} heures par semaine, et si n&eacute;cessaire des heures suppl&eacute;mentaires en fonction de votre</p>
							<p>responsabilit&eacute;s.</p>
							<p>6. Cong&eacute;s/Vacances</p>
							<p>6.1 Vous avez droit &agrave; un cong&eacute; occasionnel de 12 jours.</p>
							<p>6.2 Vous avez droit &agrave; 12 jours ouvrables de cong&eacute; de maladie pay&eacute;.</p>
							<p>6.3 La Soci&eacute;t&eacute; communiquera une liste des jours f&eacute;ri&eacute;s d&eacute;clar&eacute;s au d&eacute;but de chaque ann&eacute;e.</p>
							<p>7. Nature des fonctions</p>
							<p>Vous ex&eacute;cuterez au mieux de vos capacit&eacute;s toutes les t&acirc;ches inh&eacute;rentes &agrave; votre poste et les t&acirc;ches suppl&eacute;mentaires que lentreprise</p>
							<p>peut faire appel &agrave; vous pour effectuer, de temps &agrave; autre. Vos fonctions sp&eacute;cifiques sont &eacute;nonc&eacute;es &agrave; lannexe II ci-jointe.</p>
							<p>8. Biens sociaux</p>
							<p>Vous maintiendrez toujours en bon &eacute;tat les biens de la Soci&eacute;t&eacute;, qui peuvent vous &ecirc;tre confi&eacute;s pour un usage officiel au cours de votre</p>
							<p>votre emploi, et doit restituer tous ces biens &agrave; la Soci&eacute;t&eacute; avant labandon de votre charge, &agrave; d&eacute;faut de quoi le co&ucirc;t</p>
							<p>de m&ecirc;me seront r&eacute;cup&eacute;r&eacute;s aupr&egrave;s de vous par la Soci&eacute;t&eacute;.</p>
							<p>9. Emprunter/accepter des cadeaux</p>
							<p>Vous nemprunterez ni naccepterez dargent, de cadeau, de r&eacute;compense ou de compensation pour vos gains personnels ou vous placerez autrement</p>
							<p>sous obligation p&eacute;cuniaire envers toute personne/client avec qui vous pourriez avoir des relations officielles.</p>
							<p>10. R&eacute;siliation</p>
							<p>10.1 Votre nomination peut &ecirc;tre r&eacute;sili&eacute;e par la Soci&eacute;t&eacute;, sans aucune raison, en vous donnant au moins [Pr&eacute;Avis] mois avant</p>
							<p>un pr&eacute;avis &eacute;crit ou un salaire en tenant lieu. Aux fins de la pr&eacute;sente clause, salaire sentend du salaire de base.</p>
							<p>10.2 Vous pouvez r&eacute;silier votre emploi au sein de la Soci&eacute;t&eacute;, sans motif, en donnant au moins [Avis &agrave; lemploy&eacute;]</p>
							<p>mois de pr&eacute;avis ou de salaire pour la p&eacute;riode non &eacute;pargn&eacute;e, restant apr&egrave;s r&eacute;gularisation des cong&eacute;s en attente, &agrave; la date.</p>
							<p>10.3 La Soci&eacute;t&eacute; se r&eacute;serve le droit de r&eacute;silier votre emploi sans pr&eacute;avis ni indemnit&eacute; de licenciement.</p>
							<p>sil a des motifs raisonnables de croire que vous &ecirc;tes coupable dinconduite ou de n&eacute;gligence, ou que vous avez commis une violation fondamentale de</p>
							<p>contrat, ou caus&eacute; une perte &agrave; la Soci&eacute;t&eacute;.</p>
							<p>10. 4 &Agrave; la fin de votre emploi pour quelque raison que ce soit, vous restituerez &agrave; la Soci&eacute;t&eacute; tous les biens ; document, et</p>
							<p>papier, &agrave; la fois loriginal et les copies de celui-ci, y compris les &eacute;chantillons, la litt&eacute;rature, les contrats, les dossiers, les listes, les dessins, les plans,</p>
							<p>lettres, notes, donn&eacute;es et similaires; et Informations confidentielles, en votre possession ou sous votre contr&ocirc;le relatives &agrave; votre</p>
							<p>lemploi ou aux affaires commerciales des clients.</p>
							<p>11. Informations confidentielles</p>
							<p>11. 1 Au cours de votre emploi au sein de la Soci&eacute;t&eacute;, vous consacrerez tout votre temps, votre attention et vos comp&eacute;tences au mieux de vos capacit&eacute;s pour</p>
							<p>son affaire. Vous ne devez pas, directement ou indirectement, vous engager ou vous associer &agrave;, &ecirc;tre li&eacute; &agrave;, concern&eacute;, employ&eacute; ou</p>
							<p>temps ou poursuivre quelque programme d&eacute;tudes que ce soit, sans lautorisation pr&eacute;alable de la Soci&eacute;t&eacute;. engag&eacute; dans toute autre entreprise ou</p>
							<p>activit&eacute;s ou tout autre poste ou travail &agrave; temps partiel ou poursuivre des &eacute;tudes quelconques, sans lautorisation pr&eacute;alable du</p>
							<p>Compagnie.</p>
							<p>11.2 Vous devez toujours maintenir le plus haut degr&eacute; de confidentialit&eacute; et garder confidentiels les dossiers, documents et autres</p>
							<p>Informations confidentielles relatives &agrave; lactivit&eacute; de la Soci&eacute;t&eacute; dont vous pourriez avoir connaissance ou qui vous seraient confi&eacute;es par tout moyen</p>
							<p>et vous nutiliserez ces registres, documents et informations que dune mani&egrave;re d&ucirc;ment autoris&eacute;e dans lint&eacute;r&ecirc;t de la Soci&eacute;t&eacute;. Pour</p>
							<p>aux fins de la pr&eacute;sente clause &laquo; Informations confidentielles &raquo; d&eacute;signe les informations sur les activit&eacute;s de la Soci&eacute;t&eacute; et celles de ses clients</p>
							<p>qui nest pas accessible au grand public et dont vous pourriez avoir connaissance dans le cadre de votre emploi. Ceci comprend,</p>
							<p>mais sans sy limiter, les informations relatives &agrave; lorganisation, ses listes de clients, ses politiques demploi, son personnel et les informations</p>
							<p>sur les produits, les processus de la Soci&eacute;t&eacute;, y compris les id&eacute;es, les concepts, les projections, la technologie, les manuels, les dessins, les conceptions,</p>
							<p>sp&eacute;cifications, et tous les papiers, curriculum vitae, dossiers et autres documents contenant de telles informations confidentielles.</p>
							<p>11.3 &Agrave; aucun moment, vous ne retirerez des informations confidentielles du bureau sans autorisation.</p>
							<p>11.4 Votre devoir de prot&eacute;ger et de ne pas divulguer</p>
							<p>Les Informations confidentielles survivront &agrave; lexpiration ou &agrave; la r&eacute;siliation du pr&eacute;sent Contrat et/ou &agrave; votre emploi au sein de la Soci&eacute;t&eacute;.</p>
							<p>11.5 La violation des conditions de cette clause vous rendra passible dun renvoi sans pr&eacute;avis en vertu de la clause ci-dessus en plus de tout</p>
							<p>autre recours que la Soci&eacute;t&eacute; peut avoir contre vous en droit.</p>
							<p>12. Avis</p>
							<p>Des avis peuvent &ecirc;tre donn&eacute;s par vous &agrave; la Soci&eacute;t&eacute; &agrave; ladresse de son si&egrave;ge social. Des avis peuvent vous &ecirc;tre donn&eacute;s par la Soci&eacute;t&eacute; &agrave;</p>
							<p>ladresse que vous avez indiqu&eacute;e dans les registres officiels.</p>''',
					'he': '''<h3 style="text-align: center;">מכתב הצטרפות</h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>נושא: מינוי לתפקיד של {designation}</p>
							<p>{employee_name} היקר,</p>
							<p>אנו שמחים להציע לך את התפקיד של {designation} ב-{app_name} theCompany בתנאים הבאים ו</p>
							<p>תנאים:</p>
							<p>1. תחילת עבודה</p>
							<p>העסקה שלך תיכנס לתוקף, החל מ-{start_date}</p>
							<p>2. כותרת תפקיד</p>
							<p>כותרת המשרה שלך תהיה {designation}.</p>
							<p>3. משכורת</p>
							<p>השכר וההטבות האחרות שלך יהיו כמפורט בתוספת 1, להלן.</p>
							<p>4. מקום הפרסום</p>
							<p>תפרסם ב-{branch}. עם זאת, ייתכן שתידרש לעבוד בכל מקום עסק שיש לחברה, או</p>
							<p>עשוי מאוחר יותר לרכוש.</p>
							<p>5. שעות עבודה</p>
							<p>ימי העבודה הרגילים הם שני עד שישי. תידרש לעבוד במשך שעות הדרושות ל</p>
							<p>מילוי נאות של חובותיך כלפי החברה. שעות העבודה הרגילות הן מ-{start_time} עד {end_time} ואתה</p>
							<p>צפוי לעבוד לא פחות מ-{total_hours} שעות בכל שבוע, ובמידת הצורך לשעות נוספות בהתאם ל</p>
							<p>אחריות.</p>
							<p>6. עזוב/חגים</p>
							<p>6.1 אתה זכאי לחופשה מזדמנת של 12 ימים.</p>
							<p>6.2 אתה זכאי ל-12 ימי עבודה של חופשת מחלה בתשלום.</p>
							<p>6.3 החברה תודיע על רשימה של חגים מוכרזים בתחילת כל שנה.</p>''',
					'it': '''<h3 style="text-align: center;">Lettera di adesione</h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>Oggetto: Appuntamento alla carica di {designation}</p>
							<p>Gentile {employee_name},</p>
							<p>Siamo lieti di offrirti la posizione di {designation} con {app_name} la "Societ&agrave;" alle seguenti condizioni e</p>
							<p>condizioni:</p>
							<p>1. Inizio del rapporto di lavoro</p>
							<p>Il tuo impiego sar&agrave; effettivo a partire da {start_date}</p>
							<p>2. Titolo di lavoro</p>
							<p>Il tuo titolo di lavoro sar&agrave; {designation}.</p>
							<p>3. Stipendio</p>
							<p>Il tuo stipendio e altri benefici saranno come indicato nell'Allegato 1, qui di seguito.</p>
							<p>4. Luogo di invio</p>
							<p>Sarai inviato a {branch}. Tuttavia, potrebbe essere richiesto di lavorare in qualsiasi luogo di attivit&agrave; che la Societ&agrave; ha, o</p>
							<p>potr&agrave; successivamente acquisire.</p>
							<p>5. Orario di lavoro</p>
							<p>I normali giorni lavorativi sono dal luned&igrave; al venerd&igrave;. Ti verr&agrave; richiesto di lavorare per le ore necessarie per il</p>
							<p>corretto adempimento dei propri doveri nei confronti della Societ&agrave;. L'orario di lavoro normale va da {start_time} a {end_time} e tu lo sei</p>
							<p>dovresti lavorare non meno di {total_hours} ore ogni settimana e, se necessario, per ore aggiuntive a seconda del tuo</p>
							<p>responsabilit&agrave;.</p>
							<p>6. Permessi/Festivit&agrave;</p>
							<p>6.1 Hai diritto a un congedo occasionale di 12 giorni.</p>
							<p>6.2 Hai diritto a 12 giorni lavorativi di congedo per malattia retribuito.</p>
							<p>6.3 La Societ&agrave; comunica all'inizio di ogni anno un elenco delle festivit&agrave; dichiarate.</p>
							<p>7. Natura degli incarichi</p>
							<p>Eseguirai al meglio delle tue capacit&agrave; tutti i compiti inerenti al tuo incarico e compiti aggiuntivi come l'azienda</p>
							<p>pu&ograve; invitarti a esibirti, di tanto in tanto. I tuoi doveri specifici sono stabiliti nell'Allegato II del presente documento.</p>
							<p>8. Propriet&agrave; aziendale</p>
							<p>Manterrai sempre in buono stato i beni dell'Azienda, che nel corso dell'anno potrebbero esserti affidati per uso ufficiale</p>
							<p>del tuo impiego, e restituirai tutti questi beni alla Societ&agrave; prima della rinuncia al tuo addebito, in caso contrario il costo</p>
							<p>degli stessi sarà recuperato dalla Societ&agrave;.</p>
							<p>9. Prendere in prestito/accettare regali</p>
							<p>Non prenderai in prestito n&eacute; accetterai denaro, dono, ricompensa o compenso per i tuoi guadagni personali da o altrimenti collocato te stesso</p>
							<p>sotto obbligazione pecuniaria nei confronti di qualsiasi persona/cliente con cui potresti avere rapporti ufficiali.</p>
							<p>10. Cessazione</p>
							<p>10.1 Il tuo incarico pu&ograve; essere risolto dalla Societ&agrave;, senza alcun motivo, dandoti non meno di [Avviso] mesi prima</p>
							<p>in forma scritta o tramite stipendio in sostituzione. Ai fini di questa clausola, per stipendio si intende lo stipendio base.</p>
							<p>10.2 Puoi terminare il tuo impiego con la Societ&agrave;, senza alcuna causa, fornendo non meno di [Avviso per il dipendente]</p>
							<p>mesi di preavviso o stipendio per il periodo non risparmiato, residuo dopo l'adeguamento delle ferie pendenti, come da data.</p>
							<p>10.3 La Societ&agrave; si riserva il diritto di terminare il rapporto di lavoro sommariamente senza alcun periodo di preavviso o pagamento di cessazione</p>
							<p>se ritiene, con motivi fondati, che tu sia colpevole di cattiva condotta o negligenza, o se hai commesso una violazione fondamentale</p>
							<p>del contratto, o se hai causato danni alla Societ&agrave;.</p>
							<p>10.4 Alla cessazione del tuo impiego, per qualsiasi motivo, restituirai alla Societ&agrave; tutti i beni; documenti, e</p>
							<p>la carta, sia in originale che in copia, inclusi eventuali campioni, letteratura, contratti, registrazioni, elenchi, disegni, progetti,</p>
							<p>lettere, note, dati e simili; e Informazioni Riservate, in tuo possesso o sotto il tuo controllo, relative al tuo</p>
							<p>impiego o agli affari dei clienti.</p>
							<p>11. Confidential Information</p>
							<p>11.1 During your employment with the Company you will devote your whole time, attention, and skill to the best of your ability for</p>
							<p>its business. You shall not, directly or indirectly, engage or associate yourself with, be connected with, concerned, employed, or</p>
							<p>time or pursue any course of study whatsoever, without the prior permission of the Company. Engaging in any other business or</p>
							<p>activities or any other post or work part-time or pursuing any course of study whatsoever, without the prior permission of the</p>
							<p>Company.</p>
							<p>11.2 You must always maintain the highest degree of confidentiality and keep as confidential the records, documents, and other&nbsp;</p>
							<p>Confidential Information relating to the business of the Company which may be known to you or confided in you by any means</p>
							<p>and you will use such records, documents and information only in a duly authorized manner in the interest of the Company. For</p>
							<p>the purposes of this clause, "Confidential Information" means information about the Company's business and that of its customers</p>
							<p>which is not available to the general public and which may be learned by you in the course of your employment. This includes,</p>
							<p>but is not limited to, information relating to the organization, its customer lists, employment policies, personnel, and information</p>
							<p>about the Company's products, processes including ideas, concepts, projections, technology, manuals, drawing, designs,&nbsp;</p>
							<p>specifications, and all papers, resumes, records and other documents containing such Confidential Information.</p>
							<p>11.3 At no time will you remove any Confidential Information from the office without permission.</p>
							<p>11.4 Your duty to safeguard and not disclose Confidential Information will survive the expiration or termination of this Agreement and/or your employment with the Company.</p>
							<p>11.5 Breach of the conditions of this clause will render you liable to summary dismissal under the clause above in addition to any</p>
							<p>other remedy the Company may have against you in law.</p>''',
					'ja': '''<h3 style="text-align: center;">入会の手紙</h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>件名: {designation} の役職への任命</p>
							<p>{employee_name} 様</p>
							<p>{app_name} の {designation} の地位を以下の条件で「会社」として提供できることをうれしく思います。</p>
							<p>条件：</p>
							<p>1. 雇用開始</p>
							<p>あなたの雇用は {start_date} から有効になります</p>
							<p>2. 役職</p>
							<p>あなたの役職は{designation}になります。</p>
							<p>3. 給与</p>
							<p>あなたの給与およびその他の福利厚生は、本明細書のスケジュール 1 に記載されているとおりです。</p>
							<p>4. 掲示場所</p>
							<p>{branch} に掲載されます。ただし、会社が所有する事業所で働く必要がある場合があります。</p>
							<p>後で取得する場合があります。</p>
							<p>5. 労働時間</p>
							<p>通常の営業日は月曜日から金曜日です。あなたは、そのために必要な時間働く必要があります。</p>
							<p>会社に対するあなたの義務の適切な遂行。通常の勤務時間は {start_time} から {end_time} までで、あなたは</p>
							<p>毎週 {total_hours} 時間以上の勤務が期待されます</p>
							<p>責任。</p>
							<p>6. 休暇・休日</p>
							<p>6.1 12 日間の臨時休暇を取得する権利があります。</p>
							<p>6.2 12 日間の有給病気休暇を取得する権利があります。</p>
							<p>6.3 当社は、毎年の初めに宣言された休日のリストを通知します。</p>''',
					'nl': '''<h3 style="text-align: center;">Deelnemende brief</h3>
							<p>{date}</p>
							<p>{employee}</p>
							<p>{address}</p>
							<p>Onderwerp: Benoeming voor de functie van {designation}</p>
							<p>Beste {employee_name},</p>
							<p>We zijn verheugd u de positie van {designation} bij {app_name} het Bedrijf aan te bieden onder de volgende voorwaarden en</p>
							<p>conditie:</p>
							<p>1. Indiensttreding</p>
							<p>Uw dienstverband gaat in op {start_date}</p>
							<p>2. Functietitel</p>
							<p>Uw functietitel wordt {designation}.</p>
							<p>3. Salaris</p>
							<p>Uw salaris en andere voordelen zijn zoals uiteengezet in Schema 1 hierbij.</p>
							<p>4. Plaats van detachering</p>
							<p>Je wordt geplaatst op {branch}. Het kan echter zijn dat u moet werken op een bedrijfslocatie die het Bedrijf heeft, of</p>
							<p>later kan verwerven.</p>
							<p>5. Werkuren</p>
							<p>De normale werkdagen zijn van maandag tot en met vrijdag. Je zal de uren moeten werken die nodig zijn voor de</p>
							<p>correcte uitvoering van uw taken jegens het bedrijf. De normale werkuren zijn van {start_time} tot {end_time} en jij bent</p>
							<p>naar verwachting niet minder dan {total_hours} uur per week, en indien nodig voor extra uren, afhankelijk van uw</p>
							<p>verantwoordelijkheden.</p>''',
					'pl': '''<h3 style="text-align: center;">Dołączanie listu</h3>
							<p>{date }</p>
							<p>{employee_name }</p>
							<p>{address }</p>
							<p>Dotyczy: mianowania na stanowisko {designation}</p>
							<p>Szanowny {employee_name },</p>
							<p>Mamy przyjemność zaoferować Państwu, stanowisko {designation} z {app_name } "Sp&oacute;łka" na poniższych warunkach i</p>
							<p>warunki:</p>
							<p>1. Rozpoczęcie pracy</p>
							<p>Twoje zatrudnienie będzie skuteczne, jak na {start_date }</p>
							<p>2. Tytuł zadania</p>
							<p>Tw&oacute;j tytuł pracy to {designation}.</p>
							<p>3. Salary</p>
							<p>Twoje wynagrodzenie i inne świadczenia będą określone w Zestawieniu 1, do niniejszego rozporządzenia.</p>
							<p>4. Miejsce delegowania</p>
							<p>Użytkownik zostanie opublikowany w {branch }. Użytkownik może jednak być zobowiązany do pracy w dowolnym miejscu prowadzenia działalności, kt&oacute;re Sp&oacute;łka posiada, lub może p&oacute;źniej nabyć.</p>
							<p>5. Godziny pracy</p>
							<p>Normalne dni robocze są od poniedziałku do piątku. Będziesz zobowiązany do pracy na takie godziny, jakie są niezbędne do prawidłowego wywiązania się ze swoich obowiązk&oacute;w wobec Sp&oacute;łki. Normalne godziny pracy to {start_time } do {end_time }, a użytkownik oczekuje, że będzie pracować nie mniej niż {total_hours } godzin tygodniowo, a jeśli to konieczne, przez dodatkowe godziny w zależności od Twojego</p>
							<p>odpowiedzialności.</p>''',
					'pt': '''<h3 style="text-align: center;">Carta De Ades&atilde;o</h3>
			<p>{data}</p>
			<p>{employee_name}</p>
			<p>{address}</p>
			
			<p>Assunto: Nomea&ccedil;&atilde;o para o cargo de {designation}</p>
			<p>Querido {employee_name},</p>
			
			<p>Temos o prazer de oferec&ecirc;-lo, a posi&ccedil;&atilde;o de {designation} com {app_name} a Empresa nos seguintes termos e</p>
			<p>condi&ccedil;&otilde;es:</p>
			
			<p>1. Comentamento do emprego</p>
			<p>Seu emprego ser&aacute; efetivo, a partir de {start_date}</p>
			
			<p>2. T&iacute;tulo do emprego</p>
			<p>Seu cargo de trabalho ser&aacute; {designation}.</p>
			
			<p>3. Sal&aacute;rio</p>
			<p>Seu sal&aacute;rio e outros benef&iacute;cios ser&atilde;o conforme estabelecido no Planejamento 1, hereto.</p>
			
			<p>4. Local de postagem</p>
			<p>Voc&ecirc; ser&aacute; postado em {branch}. Voc&ecirc; pode, no entanto, ser obrigado a trabalhar em qualquer local de neg&oacute;cios que a Empresa tenha, ou possa posteriormente adquirir.</p>
			
			<p>5. Horas de Trabalho</p>
			<p>Os dias normais de trabalho s&atilde;o de segunda a sexta-feira. Voc&ecirc; ser&aacute; obrigado a trabalhar por tais horas, conforme necess&aacute;rio para a quita&ccedil;&atilde;o adequada de suas fun&ccedil;&otilde;es para a Companhia. As horas de trabalho normais s&atilde;o de {start_time} para {end_time} e voc&ecirc; deve trabalhar n&atilde;o menos de {total_horas} horas semanais, e se necess&aacute;rio para horas adicionais dependendo do seu</p>
			<p>responsabilidades.</p>
			
			<p>6. Leave / Holidays</p>
			<p>6,1 Voc&ecirc; tem direito a licen&ccedil;a casual de 12 dias.</p>
			<p>6,2 Voc&ecirc; tem direito a 12 dias &uacute;teis de licen&ccedil;a remunerada remunerada.</p>
			<p>6,3 Companhia notificar&aacute; uma lista de feriados declarados no in&iacute;cio de cada ano.&nbsp;</p>
			
			<p>7. Natureza dos deveres</p>
			<p>Voc&ecirc; ir&aacute; executar ao melhor da sua habilidade todos os deveres como s&atilde;o inerentes ao seu cargo e tais deveres adicionais como a empresa pode ligar sobre voc&ecirc; para executar, de tempos em tempos. Os seus deveres espec&iacute;ficos s&atilde;o estabelecidos no Hereto do Planejamento II.</p>
			
			<p>8. Propriedade da empresa</p>
			<p>Voc&ecirc; sempre manter&aacute; em bom estado propriedade Empresa, que poder&aacute; ser confiada a voc&ecirc; para uso oficial durante o curso de</p>
			<p>o seu emprego, e devolver&aacute; toda essa propriedade &agrave; Companhia antes de abdicar de sua acusa&ccedil;&atilde;o, falhando qual o custo do mesmo ser&aacute; recuperado de voc&ecirc; pela Companhia.</p>
			
			<p>9. Borremir / aceitar presentes</p>
			<p>Voc&ecirc; n&atilde;o vai pedir empr&eacute;stimo ou aceitar qualquer dinheiro, presente, recompensa ou indeniza&ccedil;&atilde;o por seus ganhos pessoais de ou de outra forma colocar-se sob obriga&ccedil;&atilde;o pecuni&aacute;ria a qualquer pessoa / cliente com quem voc&ecirc; pode estar tendo rela&ccedil;&otilde;es oficiais.</p>
			
			<p>10. Termina&ccedil;&atilde;o</p>
			<p>10,1 Sua nomea&ccedil;&atilde;o pode ser rescindida pela Companhia, sem qualquer raz&atilde;o, dando-lhe n&atilde;o menos do que [aviso] meses de aviso pr&eacute;vio por escrito ou de sal&aacute;rio em lieu deste. Para efeito da presente cl&aacute;usula, o sal&aacute;rio deve significar sal&aacute;rio base.</p>
			<p>10,2 Voc&ecirc; pode rescindir seu emprego com a Companhia, sem qualquer causa, ao dar nada menos que [Aviso de contrata&ccedil;&atilde;o] meses de aviso pr&eacute;vio ou sal&aacute;rio para o per&iacute;odo n&atilde;o salvo, deixado ap&oacute;s ajuste de folhas pendentes, conforme data de encontro.</p>
			<p>10,3 Empresa reserva-se o direito de rescindir o seu emprego sumariamente sem qualquer prazo de aviso ou de rescis&atilde;o se tiver terreno razo&aacute;vel para acreditar que voc&ecirc; &eacute; culpado de m&aacute; conduta ou neglig&ecirc;ncia, ou tenha cometido qualquer viola&ccedil;&atilde;o fundamental de contrato, ou tenha causado qualquer perda para a Empresa.&nbsp;</p>
			<p>10. 4 Sobre a rescis&atilde;o do seu emprego por qualquer motivo, voc&ecirc; retornar&aacute; para a Empresa todos os bens; documentos e&nbsp;</p>
			<p>papel, tanto originais como c&oacute;pias dos mesmos, incluindo quaisquer amostras, literatura, contratos, registros, listas, desenhos, plantas,</p>
			<p>cartas, notas, dados e semelhantes; e Informa&ccedil;&otilde;es Confidenciais, em sua posse ou sob seu controle relacionado ao seu emprego ou aos neg&oacute;cios de neg&oacute;cios dos clientes.&nbsp; &nbsp;</p>
			
			<p>11. Informa&ccedil;&otilde;es Confidenciais</p>
			<p>11. 1 Durante o seu emprego com a Companhia voc&ecirc; ir&aacute; dedicar todo o seu tempo, aten&ccedil;&atilde;o e habilidade para o melhor de sua capacidade de</p>
			<p>o seu neg&oacute;cio. Voc&ecirc; n&atilde;o deve, direta ou indiretamente, se envolver ou associar-se com, estar conectado com, preocupado, empregado, ou tempo ou prosseguir qualquer curso de estudo, sem a permiss&atilde;o pr&eacute;via do Company.engajado em qualquer outro neg&oacute;cio ou atividades ou qualquer outro cargo ou trabalho parcial ou prosseguir qualquer curso de estudo, sem a permiss&atilde;o pr&eacute;via do</p>
			<p>Empresa.</p>
			<p>11,2 &Eacute; preciso manter sempre o mais alto grau de confidencialidade e manter como confidenciais os registros, documentos e outros&nbsp;</p>
			<p>Informa&ccedil;&otilde;es confidenciais relativas ao neg&oacute;cio da Companhia que possam ser conhecidas por voc&ecirc; ou confiadas em voc&ecirc; por qualquer meio e utilizar&atilde;o tais registros, documentos e informa&ccedil;&otilde;es apenas de forma devidamente autorizada no interesse da Companhia. Para efeitos da presente cl&aacute;usula "Informa&ccedil;&otilde;es confidenciais" significa informa&ccedil;&atilde;o sobre os neg&oacute;cios da Companhia e a dos seus clientes que n&atilde;o est&aacute; dispon&iacute;vel para o p&uacute;blico em geral e que poder&aacute; ser aprendida por voc&ecirc; no curso do seu emprego. Isso inclui,</p>
			<p>mas n&atilde;o se limita a, informa&ccedil;&otilde;es relativas &agrave; organiza&ccedil;&atilde;o, suas listas de clientes, pol&iacute;ticas de emprego, pessoal, e informa&ccedil;&otilde;es sobre os produtos da Companhia, processos incluindo ideias, conceitos, proje&ccedil;&otilde;es, tecnologia, manuais, desenho, desenhos,&nbsp;</p>
			<p>especifica&ccedil;&otilde;es, e todos os pap&eacute;is, curr&iacute;culos, registros e outros documentos que contenham tais Informa&ccedil;&otilde;es Confidenciais.</p>
			<p>11,3 Em nenhum momento, voc&ecirc; remover&aacute; quaisquer Informa&ccedil;&otilde;es Confidenciais do escrit&oacute;rio sem permiss&atilde;o.</p>
			<p>11,4 O seu dever de salvaguardar e n&atilde;o os desclos</p>
			<p>Informa&ccedil;&otilde;es Confidenciais sobreviver&atilde;o &agrave; expira&ccedil;&atilde;o ou &agrave; rescis&atilde;o deste Contrato e / ou do seu emprego com a Companhia.</p>
			<p>11,5 Viola&ccedil;&atilde;o das condi&ccedil;&otilde;es desta cl&aacute;usula ir&aacute; torn&aacute;-lo sujeito a demiss&atilde;o sum&aacute;ria sob a cl&aacute;usula acima, al&eacute;m de qualquer outro rem&eacute;dio que a Companhia possa ter contra voc&ecirc; em lei.</p>
			
			<p>12. Notices</p>
			<p>Os avisos podem ser conferidos por voc&ecirc; &agrave; Empresa em seu endere&ccedil;o de escrit&oacute;rio registrado. Os avisos podem ser conferidos pela Companhia a voc&ecirc; no endere&ccedil;o intimado por voc&ecirc; nos registros oficiais.</p>
			
			<p>13. Aplicabilidade da Pol&iacute;tica da Empresa</p>
			<p>A Companhia tem direito a fazer declara&ccedil;&otilde;es de pol&iacute;tica de tempos em tempos relativos a mat&eacute;rias como licen&ccedil;a de licen&ccedil;a, maternidade</p>
			<p>sair, benef&iacute;cios dos empregados, horas de trabalho, pol&iacute;ticas de transfer&ecirc;ncia, etc., e pode alterar o mesmo de vez em quando a seu exclusivo crit&eacute;rio.</p>
			<p>Todas essas decis&otilde;es de pol&iacute;tica da Companhia devem ser vinculativas para si e substituir&atilde;o este Acordo nessa medida.</p>
			
			<p>14. Direito / Jurisdi&ccedil;&atilde;o</p>
			<p>Seu emprego com a Companhia est&aacute; sujeito &agrave;s leis do Pa&iacute;s. Todas as disputas est&atilde;o sujeitas &agrave; jurisdi&ccedil;&atilde;o do Tribunal Superior</p>
			<p>Gujarat apenas.</p>
			
			<p>15. Aceita&ccedil;&atilde;o da nossa oferta</p>
			<p>Por favor, confirme sua aceita&ccedil;&atilde;o deste Contrato de Emprego assinando e retornando a c&oacute;pia duplicada.</p>
			<p>N&oacute;s acolhemos voc&ecirc; e estamos ansiosos para receber sua aceita&ccedil;&atilde;o e para trabalhar com voc&ecirc;.</p>
			<p>Seu Sinceramente,</p>
			<p>{app_name}</p>
			<p>{data}</p>''',
					'ru': '''<h3 style="text-align: center;">Присоединение к письму</h3>
							<p>{date}</p>
							<p>{ employee_name }</p>
							<p>{address}</p>
							<p>Тема: Назначение на должность {designation}</p>
							<p>Уважаемый { employee_name },</p>
							<p>Мы рады предложить Вам, позицию {designation} с { app_name } Компания на следующих условиях и</p>
							<p>условия:</p>
							<p>1. Начало работы</p>
							<p>Ваше трудоустройство будет эффективным, начиная с { start_date }</p>
							<p>2. Название должности</p>
							<p>Ваш заголовок задания будет {designation}.</p>
							<p>3. Зарплата</p>
							<p>Ваши оклады и другие пособия будут установлены в соответствии с расписанием, изложенным в приложении 1 к настоящему.</p>
							<p>4. Место размещения</p>
							<p>Вы будете работать в { branch }. Вы, однако, можете работать в любом месте, которое компания имеет или может впоследствии приобрести.</p>
							<p>5. Часы работы</p>
							<p>Обычные рабочие дни – с понедельника по пятницу. Вы должны будете работать в течение таких часов, как это необходимо для надлежащего выполнения Ваших обязанностей перед компанией. Обычные рабочие часы – от { start_time } до { end_time }, и вы, как ожидается, будете работать не менее { total_hours } часов каждую неделю, и при необходимости – дополнительные часы в зависимости от вашего</p>
							<p>ответственности.</p>
							<p>6. Отпуск/Праздники</p>
							<p>6.1 Вы имеете право на случайный отпуск продолжительностью 12 дней.</p>
							<p>6.2 Вы имеете право на 12 рабочих дней оплачиваемого отпуска по болезни.</p>
							<p>6.3 Компания в начале каждого года уведомляет об объявленных праздниках.&nbsp;</p>
							<p>7. Характер обязанностей</p>
							<p>Вы будете выполнять все обязанности, присущие вам, и такие дополнительные обязанности, которые компания может призвать к вам, время от времени. Ваши конкретные обязанности изложены в приложении II к настоящему.</p>
							<p>8. Свойство компании</p>
							<p>Вы всегда будете поддерживать в хорошем состоянии имущество Компании, которое может быть доверено Вам для служебного пользования в течение</p>
							<p>вашей занятости, и возвратите все это имущество Компании до отказа от вашего заряда, при отсутствии которого стоимость одного и того же имущества будет взыскана с Вас компанией.</p>
							<p>9. Боровить/принять подарки</p>
							<p>Вы не будете брать взаймы или принимать какие-либо деньги, подарки, вознаграждение или компенсацию за ваши личные доходы от или в ином месте под денежный долг любому лицу/клиенту, с которым у вас могут быть официальные сделки.</p>
							<p>10. Прекращение</p>
							<p>10.1 Ваше назначение может быть прекращено компанией без каких-либо оснований, предоставляя Вам не менее [Уведомление] месяцев, предшествующих письменному уведомлению или окладу вместо него. Для целей данного положения заработная плата означает базовый оклад.</p>
							<p>10.2 Вы можете прекратить свою трудовую деятельность с компанией без каких-либо причин, предоставляя не менее [Employee Notice] месяцев предварительного уведомления или оклад за несохраненный период, оставшийся после корректировки отложенных листьев, как на сегодняшний день.</p>
							<p>10.3 Компания оставляет за собой право прекратить вашу работу суммарно без какого-либо периода уведомления или выплаты при прекращении трудовых отношений, если у нее есть достаточные основания полагать, что вы виновны в проступке или халатности, или если вы совершили существенное нарушение договора, или причинили убытки Компании.&nbsp;</p>
							<p>10.4 При прекращении вашей работы по какой-либо причине вы вернете Компании все имущество; документы и</p>
							<p>бумагу, как оригинальные, так и их копии, включая любые образцы, литературу, контракты, записи, списки, чертежи, чертежи,</p>
							<p>письма, заметки, данные и тому подобное; и Конфиденциальная информация, находящаяся в вашем распоряжении или под вашим контролем, связанная с вашей</p>
							<p>работой или деловыми делами клиентов.</p>
							<p>11. Конфиденциальная информация</p>
							<p>11.1 Во время вашего трудоустройства с компанией Вы посвятите всё своё время, внимание и умения наилучшим образом для её бизнеса. Вы не должны, прямо или косвенно, заниматься или ассоциировать себя с чем-либо, быть связанным, вовлеченным или работать или проводить время, или заниматься каким-либо курсом обучения без предварительного разрешения Компании. Заниматься каким-либо другим бизнесом или деятельностью или работать неполный рабочий день или заниматься каким-либо курсом обучения без предварительного разрешения Компании.</p>
							<p>11.2 Вы всегда должны сохранять наивысший уровень конфиденциальности и хранить в секрете записи, документы и другую Конфиденциальную информацию, касающуюся бизнеса Компании, которая может быть вам известна или передана вам любым способом, и вы будете использовать такие записи, документы и информацию только в установленном порядке в интересах Компании. Для целей настоящей статьи "Конфиденциальная информация" означает информацию о бизнесе Компании и её клиентах, которая недоступна широкой общественности и которую вы можете узнать в процессе работы. Это включает в себя,</p>
							<p>но не ограничивается информацией, касающейся организации, её списков клиентов, трудовых политик, персонала и информации о продуктах Компании, процессах, включая идеи, концепции, прогнозы, технологии, руководства, чертежи, проекты,</p>
							<p>спецификации, а также все бумаги, резюме, записи и другие документы, содержащие такую Конфиденциальную информацию.</p>
							<p>11.3 В любое время вы не должны выносить Конфиденциальную информацию из офиса без разрешения.</p>
							<p>11.4 Ваша обязанность по защите и неразглашению Конфиденциальной информации сохраняется и после истечения срока действия или прекращения данного Соглашения и/или вашего трудоустройства в Компании.</p>
							<p>11.5 Нарушение условий данной статьи приведет к тому, что вы будете подвергнуты увольнению без предупреждения, а также к любым другим мерам, предусмотренным законом, которые Компания может принять против вас.</p>''',
					'tr': '''<h3 style="text-align: center;">Katılım Mektubu</h3>
							<p>{tarih}</p>
							<p>{çalışan_adı}</p>
							<p>{adres</p>
							<p>Konu: {tanımlama</p> görevi için randevu
							<p>Sayın {çalışan_adı},</p>
							<p>Aşağıdaki şartlar ve</p> ile Şirkette {app_name} ile {designation} konumunu size sunmaktan mutluluk duyuyoruz.</p>
							<p>koşullar:</p>
							<p>1. İşe başlama</p>
							<p>İstihdamınız {start_date}</p> itibarıyla geçerli olacak
							<p>2. İş unvanı</p>
							<p>İş unvanınız {tanımlama} olacaktır.</p>
							<p>3. maaş</p>
							<p>Maaşınız ve diğer yardımlarınız, bundan böyle Çizelge 1 de belirtildiği gibi olacaktır.</p>
							<p>4. Gönderim yeri</p>
							<p>{branch} adresinde ilan edileceksiniz. Ancak, Şirketin sahip olduğu herhangi bir işyerinde çalışmanız gerekebilir veya</p>
							<p>daha sonra edinilebilir.</p>
							<p>5. Çalışma Saatleri</p>
							<p>Normal çalışma günleri Pazartesi den Cuma ya kadardır. </p> için gerekli olan saatlerde çalışmanız istenecektir.
							<p>Şirkete karşı görevlerinizi uygun şekilde yerine getirme. Normal çalışma saatleri {start_time} ile {end_time} arasındadır ve siz</p>
							<p>Her hafta en az {total_hours} saat ve gerekirse sizin durumunuza bağlı olarak ek saat çalışması bekleniyor</p>
							<p>sorumluluklar.</p>
							<p>6. İzin/Tatiller</p>
							<p>6.1 12 günlük geçici izin hakkınız var.</p>
							<p>6.2 12 iş günü ücretli hastalık izni hakkınız var.</p>
							<p>6.3 Şirket, her yılın başında ilan edilen tatillerin listesini bildirecektir.</p>''',
					'pt-br': '''<h3 style="text-align: center;">Carta De Ades&atilde;o</h3>
        <p>{data}</p>
        <p>{employee_name}</p>
        <p>{address}</p>
        
        <p>Assunto: Nomea&ccedil;&atilde;o para o cargo de {designation}</p>
        <p>Querido {employee_name},</p>
        
        <p>Temos o prazer de oferec&ecirc;-lo, a posi&ccedil;&atilde;o de {designation} com {app_name} a Empresa nos seguintes termos e</p>
        <p>condi&ccedil;&otilde;es:</p>
        
        <p>1. Comentamento do emprego</p>
        <p>Seu emprego ser&aacute; efetivo, a partir de {start_date}</p>
        
        <p>2. T&iacute;tulo do emprego</p>
        <p>Seu cargo de trabalho ser&aacute; {designation}.</p>
        
        <p>3. Sal&aacute;rio</p>
        <p>Seu sal&aacute;rio e outros benef&iacute;cios ser&atilde;o conforme estabelecido no Planejamento 1, hereto.</p>
        
        <p>4. Local de postagem</p>
        <p>Voc&ecirc; ser&aacute; postado em {branch}. Voc&ecirc; pode, no entanto, ser obrigado a trabalhar em qualquer local de neg&oacute;cios que a Empresa tenha, ou possa posteriormente adquirir.</p>
        
        <p>5. Horas de Trabalho</p>
        <p>Os dias normais de trabalho s&atilde;o de segunda a sexta-feira. Voc&ecirc; ser&aacute; obrigado a trabalhar por tais horas, conforme necess&aacute;rio para a quita&ccedil;&atilde;o adequada de suas fun&ccedil;&otilde;es para a Companhia. As horas de trabalho normais s&atilde;o de {start_time} para {end_time} e voc&ecirc; deve trabalhar n&atilde;o menos de {total_horas} horas semanais, e se necess&aacute;rio para horas adicionais dependendo do seu</p>
        <p>responsabilidades.</p>'''		
        }
        for lang, content in default_template.items():
            JoiningLetter.objects.create(
                lang=lang,
                content=content,
                created_by=2
            )

  @staticmethod
  def default_joining_letter_register(user_id):
        """
        Creates default joining letter templates for a registered user.
        Supply your default template mapping language codes to contents.
        """
        default_template = {
					'ar': '''<h2 style="text-align: center;"><strong>خطاب الانضمام</strong></h2>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>الموضوع: موعد لوظيفة {designation}</p>
							<p>عزيزي {employee_name} ،</p>
							<p>يسعدنا أن نقدم لك منصب {designation} مع {app_name} "الشركة" وفقًا للشروط التالية و</p>
							<p>الظروف:</p>
							<p>1. بدء العمل</p>
							<p>سيصبح عملك ساريًا اعتبارًا من {start_date}</p>
							<p>2. المسمى الوظيفي</p>
							<p>سيكون المسمى الوظيفي الخاص بك هو {designation}.</p>
							<p>3. الراتب</p>
							<p>سيكون راتبك والمزايا الأخرى على النحو المبين في الجدول 1 ، طيه.</p>
							<p>4. مكان الإرسال</p>
							<p>سيتم إرسالك إلى {branch}. ومع ذلك ، قد يُطلب منك العمل في أي مكان عمل تمتلكه الشركة ، أو</p>
							<p>قد تحصل لاحقًا.</p>
							<p>5. ساعات العمل</p>
							<p>أيام العمل العادية هي من الاثنين إلى الجمعة. سيُطلب منك العمل لساعات حسب الضرورة لـ</p>
							<p>أداء واجباتك على النحو الصحيح تجاه الشركة. ساعات العمل العادية من {start_time} إلى {end_time} وأنت</p>
							<p>من المتوقع أن يعمل ما لا يقل عن {total_hours} ساعة كل أسبوع ، وإذا لزم الأمر لساعات إضافية اعتمادًا على</p>
							<p>المسؤوليات.</p>
							<p>6. الإجازة / العطل</p>
							<p>6.1 يحق لك الحصول على إجازة غير رسمية مدتها 12 يومًا.</p>
							<p>6.2 يحق لك الحصول على إجازة مرضية مدفوعة الأجر لمدة 12 يوم عمل.</p>
							<p>6.3 تخطر الشركة بقائمة الإجازات المعلنة في بداية كل عام.</p>
							<p>7. طبيعة الواجبات</p>
							<p>ستقوم بأداء أفضل ما لديك من واجبات متأصلة في منصبك ومهام إضافية مثل الشركة</p>
							<p>قد يدعوك لأداء ، من وقت لآخر. واجباتك المحددة منصوص عليها في الجدول الثاني بهذه الرسالة.</p>
							<p>8. ممتلكات الشركة</p>
							<p>ستحافظ دائمًا على ممتلكات الشركة في حالة جيدة ، والتي قد يتم تكليفك بها للاستخدام الرسمي خلال فترة عملها</p>
							<p>عملك ، ويجب أن تعيد جميع هذه الممتلكات إلى الشركة قبل التخلي عن الرسوم الخاصة بك ، وإلا فإن التكلفة</p>
							<p>نفس الشيء سوف تسترده منك الشركة.</p>
							<p>9. الاقتراض / قبول الهدايا</p>
							<p>لن تقترض أو تقبل أي أموال أو هدية أو مكافأة أو تعويض مقابل مكاسبك الشخصية من أو تضع نفسك بأي طريقة أخرى</p>
							<p>بموجب التزام مالي تجاه أي شخص / عميل قد تكون لديك تعاملات رسمية معه.</p>
							<p>10. الإنهاء</p>
							<p>10.1 يمكن للشركة إنهاء موعدك ، دون أي سبب ، من خلال إعطائك ما لا يقل عن [إشعار] قبل أشهر</p>
							<p>إشعار خطي أو راتب بدلاً منه. لغرض هذا البند ، يقصد بالراتب المرتب الأساسي.</p>
							<p>10.2 إنهاء عملك مع الشركة ، دون أي سبب ، من خلال تقديم ما لا يقل عن إشعار الموظف</p>
							<p>أشهر الإخطار أو الراتب عن الفترة غير المحفوظة ، المتبقية بعد تعديل الإجازات المعلقة ، كما في التاريخ.</p>
							<p>10.3 تحتفظ الشركة بالحق في إنهاء عملك بإيجاز دون أي فترة إشعار أو مدفوعات إنهاء</p>
							<p>إذا كان لديه سبب معقول للاعتقاد بأنك مذنب بسوء السلوك أو الإهمال ، أو ارتكبت أي خرق جوهري لـ</p>
							<p>العقد ، أو تسبب في أي خسارة للشركة.</p>
							<p>10.4 عند إنهاء عملك لأي سبب من الأسباب ، ستعيد إلى الشركة جميع ممتلكاتك ؛ المستندات و</p>
							<p>الأوراق الأصلية ونسخها ، بما في ذلك أي عينات ، وأدبيات ، وعقود ، وسجلات ، وقوائم ، ورسومات ، ومخططات ،</p>
							<p>الرسائل والملاحظات والبيانات وما شابه ذلك ؛ والمعلومات السرية التي بحوزتك أو تحت سيطرتك والمتعلقة بك</p>
							<p>التوظيف أو الشؤون التجارية للعملاء.</p>
							<p>11. المعلومات السرية</p>
							<p>11.1 أثناء عملك في الشركة ، سوف تكرس وقتك واهتمامك ومهارتك كلها بأفضل ما لديك من قدرات</p>
							<p>عملها. لا يجوز لك ، بشكل مباشر أو غير مباشر ، الانخراط أو الارتباط بنفسك ، أو الارتباط به ، أو القلق ، أو التوظيف ، أو</p>
							<p>الوقت أو متابعة أي دورة دراسية على الإطلاق ، دون الحصول على إذن مسبق من الشركة أو الانخراط في أي عمل آخر أو</p>
							<p>الأنشطة أو أي وظيفة أخرى أو العمل بدوام جزئي أو متابعة أي دورة دراسية على الإطلاق ، دون إذن مسبق من</p>
							<p>شركة.</p>
							<p>11.1 أثناء عملك في الشركة ، سوف تكرس وقتك واهتمامك ومهارتك كلها بأفضل ما لديك من قدرات</p>
							<p>عملها. لا يجوز لك ، بشكل مباشر أو غير مباشر ، الانخراط أو الارتباط بنفسك ، أو الارتباط به ، أو القلق ، أو التوظيف ، أو</p>
							<p>الوقت أو متابعة أي دورة دراسية على الإطلاق ، دون الحصول على إذن مسبق من الشركة أو الانخراط في أي عمل آخر أو</p>
							<p>الأنشطة أو أي وظيفة أخرى أو العمل بدوام جزئي أو متابعة أي دورة دراسية على الإطلاق ، دون إذن مسبق من</p>
							<p>شركة.</p>
							<p>11.2 يجب عليك دائمًا الحفاظ على أعلى درجة من السرية والحفاظ على سرية السجلات والوثائق وغيرها</p>
							<p>المعلومات السرية المتعلقة بأعمال الشركة والتي قد تكون معروفة لك أو مخولة لك بأي وسيلة</p>
							<p>ولن تستخدم هذه السجلات والمستندات والمعلومات إلا بالطريقة المصرح بها حسب الأصول لصالح الشركة. إلى عن على</p>
							<p>أغراض هذا البند "المعلومات السرية" تعني المعلومات المتعلقة بأعمال الشركة وعملائها</p>
							<p>التي لا تتوفر لعامة الناس والتي قد تتعلمها أثناء عملك. هذا يشمل،</p>
							<p>على سبيل المثال لا الحصر ، المعلومات المتعلقة بالمنظمة وقوائم العملاء وسياسات التوظيف والموظفين والمعلومات</p>
							<p>حول منتجات الشركة وعملياتها بما في ذلك الأفكار والمفاهيم والإسقاطات والتكنولوجيا والكتيبات والرسم والتصاميم ،</p>
							<p>المواصفات وجميع الأوراق والسير الذاتية والسجلات والمستندات الأخرى التي تحتوي على هذه المعلومات السرية.</p>
							<p>11.3 لن تقوم في أي وقت بإزالة أي معلومات سرية من المكتب دون إذن.</p>
							<p>11.4 واجبك في الحماية وعدم الإفشاء</p>
							<p>تظل المعلومات السرية سارية بعد انتهاء أو إنهاء هذه الاتفاقية و / أو عملك مع الشركة.</p>
							<p>11.5 سوف يجعلك خرق شروط هذا البند عرضة للفصل بإجراءات موجزة بموجب الفقرة أعلاه بالإضافة إلى أي</p>
							<p>أي تعويض آخر قد يكون للشركة ضدك في القانون.</p>
							<p>12. الإخطارات</p>
							<p>يجوز لك إرسال إخطارات إلى الشركة على عنوان مكتبها المسجل. يمكن أن ترسل لك الشركة إشعارات على</p>
							<p>العنوان الذي أشرت إليه في السجلات الرسمية.</p>''',
					'zh': '''<h3 style="text-align: center;">加入信</h3>
							<p>{日期}</p>
							<p>{employee_name}</p>
							<p>{地址}</p>
							<p>主题：任命 {designation} 职位</p>
							<p>亲爱的{employee_name}，</p>
							<p>我们很高兴根据以下条款向您提供 {app_name} theCompany 的 {designation} 职位，并且</p>
							<p>条件：</p>
							<p>1.开始就业</p>
							<p>您的雇佣关系将于 {start_date}起生效</p>
							<p>2.职位名称</p>
							<p>您的职位名称为{designation}。</p>
							<p>3.薪资</p>
							<p>您的工资和其他福利将在附表 1 中列出。</p>
							<p>4.发帖地点</p>
							<p>您将被调往{branch}。但是，您可能需要在公司拥有的任何营业地点工作，或者</p>
							<p>稍后可能会获得。</p>
							<p>5.工作时间</p>
							<p>正常工作日为周一至周五。您将需要在必要的时间内工作</p>
							<p>正确履行您对公司的职责。正常工作时间为 {start_time} 至 {end_time}，您</p>
							<p>预计每周工作不少于 {total_hours} 小时，如有必要，可根据您的情况增加工作时间</p>
							<p>职责。</p>
							<p>6.休假/节假日</p>
							<p>6.1 您有权享受 12 天的事假。</p>
							<p>6.2 您有权享受 12 个工作日的带薪病假。</p>
							<p>6.3 公司应在每年年初公布已宣布的假期清单。</p>''',
					'da': '''<h3 style="text-align: center;"><strong>Tilslutningsbrev</strong></h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>Emne: Udn&aelig;vnelse til stillingen som {designation}</p>
							<p>K&aelig;re {employee_name}</p>
							<p>Vi er glade for at kunne tilbyde dig stillingen som {designation} hos {app_name} "Virksomheden" p&aring; f&oslash;lgende vilk&aring;r og</p>
							<p>betingelser:</p>
							<p>1. P&aring;begyndelse af ans&aelig;ttelse</p>
							<p>Din ans&aelig;ttelse tr&aelig;der i kraft fra {start_date}</p>
							<p>2. Jobtitel</p>
							<p>Din jobtitel vil v&aelig;re {designation}.</p>
							<p>3. L&oslash;n</p>
							<p>Din l&oslash;n og andre goder vil v&aelig;re som angivet i skema 1, hertil.</p>
							<p>4. Udstationeringssted</p>
							<p>Du vil blive sl&aring;et op p&aring; {branch}. Du kan dog blive bedt om at arbejde p&aring; ethvert forretningssted, som virksomheden har, eller</p>
							<p>senere kan erhverve.</p>
							<p>5. Arbejdstimer</p>
							<p>De normale arbejdsdage er mandag til fredag. Du vil blive forpligtet til at arbejde i de timer, som er n&oslash;dvendige for</p>
							<p>beh&oslash;rig varetagelse af dine pligter over for virksomheden. Den normale arbejdstid er fra {start_time} til {end_time}, og det er du</p>
							<p>forventes at arbejde ikke mindre end {total_hours} timer hver uge, og om n&oslash;dvendigt yderligere timer afh&aelig;ngigt af din</p>
							<p>ansvar.</p>''',
					'de': '''<h3 style="text-align: center;"><strong>Beitrittsbrief</strong></h3>
        <p>{date}</p>
        <p>{employee_name}</p>
        <p>{address}</p>
        
        <p>Betreff: Ernennung f&uuml;r die Stelle von {designation}</p>
        
        
        
        
        <p>Sehr geehrter {employee_name},</p>
        
        
        
        
        <p>Wir freuen uns, Ihnen die Position von {designation} bei {app_name} dem &bdquo;Unternehmen&ldquo; zu den folgenden Bedingungen anbieten zu k&ouml;nnen</p>
        <p>Bedingungen:</p>
        
        
        <p>1. Aufnahme des Arbeitsverh&auml;ltnisses</p>
        <p>Ihre Anstellung gilt ab dem {start_date}</p>
        
        
        <p>2. Berufsbezeichnung</p>
        <p>Ihre Berufsbezeichnung lautet {designation}.</p>
        
        
        <p>3. Gehalt</p>
        <p>Ihr Gehalt und andere Leistungen sind in Anhang 1 zu diesem Dokument aufgef&uuml;hrt.</p>
        
        
        <p>4. Postort</p>
        <p>Sie werden bei {branch} eingestellt. Es kann jedoch erforderlich sein, dass Sie an jedem Gesch&auml;ftssitz arbeiten, den das Unternehmen hat, oder</p>
        <p>sp&auml;ter erwerben kann.</p>
        
        
        <p>5. Arbeitszeit</p>
        <p>Die normalen Arbeitstage sind Montag bis Freitag. Sie m&uuml;ssen so viele Stunden arbeiten, wie es f&uuml;r die erforderliche</p>
        <p>ordnungsgem&auml;&szlig;e Erf&uuml;llung Ihrer Pflichten gegen&uuml;ber dem Unternehmen notwendig sind. Die normalen Arbeitszeiten sind von {start_time} bis {end_time} und Sie werden</p>
        <p>voraussichtlich nicht weniger als {total_hours} Stunden pro Woche arbeiten, und falls erforderlich, auch zus&auml;tzliche Stunden leisten.</p>''',
					'en': '''<h3 style="text-align: center;">Joining Letter</h3>
					<p>{date}</p>
					<p>{employee_name}</p>
					<p>{address}</p>
					<p>Subject: Appointment for the post of {designation}</p>
					<p>Dear {employee_name},</p>
					<p>We are pleased to offer you the position of {designation} with {app_name} theCompany on the following terms and</p>
					<p>conditions:</p>
					<p>1. Commencement of employment</p>
					<p>Your employment will be effective, as of {start_date}</p>
					<p>2. Job title</p>
					<p>Your job title will be{designation}.</p>
					<p>3. Salary</p>
					<p>Your salary and other benefits will be as set out in Schedule 1, hereto.</p>
					<p>4. Place of posting</p>
					<p>You will be posted at {branch}. You may however be required to work at any place of business which the Company has, or</p>
					<p>may later acquire.</p>
					<p>5. Hours of Work</p>
					<p>The normal working days are Monday through Friday. You will be required to work for such hours as necessary for the</p>
					<p>proper discharge of your duties to the Company. The normal working hours are from {start_time} to {end_time} and you are</p>
					<p>expected to work not less than {total_hours} hours each week, and if necessary for additional hours depending on your</p>
					<p>responsibilities.</p>
					<p>6. Leave/Holidays</p>
					<p>6.1 You are entitled to casual leave of 12 days.</p>
					<p>6.2 You are entitled to 12 working days of paid sick leave.</p>
					<p>6.3 The Company shall notify a list of declared holidays at the beginning of each year.</p>
					<p>7. Nature of duties</p>
					<p>You will perform to the best of your ability all the duties as are inherent in your post and such additional duties as the company</p>
					<p>may call upon you to perform, from time to time. Your specific duties are set out in Schedule II hereto.</p>
					<p>8. Company property</p>
					<p>You will always maintain in good condition Company property, which may be entrusted to you for official use during the course of</p>
					<p>your employment, and shall return all such property to the Company prior to relinquishment of your charge, failing which the cost</p>
					<p>of the same will be recovered from you by the Company.</p>
					<p>9. Borrowing/accepting gifts</p>
					<p>You will not borrow or accept any money, gift, reward, or compensation for your personal gains from or otherwise place yourself</p>
					<p>under pecuniary obligation to any person/client with whom you may be having official dealings.</p>
					<p>10. Termination</p>
					<p>10.1 Your appointment can be terminated by the Company, without any reason, by giving you not less than [Notice] months prior</p>
					<p>notice in writing or salary in lieu thereof. For the purpose of this clause, salary shall mean basic salary.</p>
					<p>10.2 You may terminate your employment with the Company, without any cause, by giving no less than [Employee Notice]</p>
					<p>months prior notice or salary for the unsaved period, left after adjustment of pending leaves, as on date.</p>
					<p>10.3 The Company reserves the right to terminate your employment summarily without any notice period or termination payment</p>
					<p>if it has reasonable ground to believe you are guilty of misconduct or negligence, or have committed any fundamental breach of</p>
					<p>contract, or caused any loss to the Company.</p>
					<p>10. 4 On the termination of your employment for whatever reason, you will return to the Company all property; documents, and</p>
					<p>paper, both original and copies thereof, including any samples, literature, contracts, records, lists, drawings, blueprints,</p>
					<p>letters, notes, data and the like; and Confidential Information, in your possession or under your control relating to your</p>
					<p>employment or to clients business affairs.</p>
					<p>11. Confidential Information</p>
					<p>11. 1 During your employment with the Company you will devote your whole time, attention, and skill to the best of your ability for</p>
					<p>its business. You shall not, directly or indirectly, engage or associate yourself with, be connected with, concerned, employed, or</p>
					<p>time or pursue any course of study whatsoever, without the prior permission of the Company.engaged in any other business or</p>
					<p>activities or any other post or work part-time or pursue any course of study whatsoever, without the prior permission of the</p>
					<p>Company.</p>
					<p>11.2 You must always maintain the highest degree of confidentiality and keep as confidential the records, documents, and other</p>
					<p>Confidential Information relating to the business of the Company which may be known to you or confided in you by any means</p>
					<p>and you will use such records, documents and information only in a duly authorized manner in the interest of the Company. For</p>
					<p>the purposes of this clauseConfidential Information means information about the Companys business and that of its customers</p>
					<p>which is not available to the general public and which may be learned by you in the course of your employment. This includes,</p>
					<p>but is not limited to, information relating to the organization, its customer lists, employment policies, personnel, and information</p>
					<p>about the Companys products, processes including ideas, concepts, projections, technology, manuals, drawing, designs,</p>
					<p>specifications, and all papers, resumes, records and other documents containing such Confidential Information.</p>
					<p>11.3 At no time, will you remove any Confidential Information from the office without permission.</p>
					<p>11.4 Your duty to safeguard and not disclos</p>
					<p>e Confidential Information will survive the expiration or termination of this Agreement and/or your employment with the Company.</p>
					<p>11.5 Breach of the conditions of this clause will render you liable to summary dismissal under the clause above in addition to any</p>
					<p>other remedy the Company may have against you in law.</p>
					<p>12. Notices</p>
					<p>Notices may be given by you to the Company at its registered office address. Notices may be given by the Company to you at</p>
					<p>the address intimated by you in the official records.</p>
					<p>13. Applicability of Company Policy</p>
					<p>The Company shall be entitled to make policy declarations from time to time pertaining to matters like leave entitlement,maternity</p>
					<p>leave, employees benefits, working hours, transfer policies, etc., and may alter the same from time to time at its sole discretion.</p>
					<p>All such policy decisions of the Company shall be binding on you and shall override this Agreement to that extent.</p>
					<p>14. Governing Law/Jurisdiction</p>
					<p>Your employment with the Company is subject to Country laws. All disputes shall be subject to the jurisdiction of High Court</p>
					<p>Gujarat only.</p>
					<p>15. Acceptance of our offer</p>
					<p>Please confirm your acceptance of this Contract of Employment by signing and returning the duplicate copy.</p>
					<p>We welcome you and look forward to receiving your acceptance and to working with you.</p>
					<p>Yours Sincerely,</p>
					<p>{app_name}</p>
					<p>{date}</p>''',
					'es': '''<h3 style="text-align: center;"><strong>Carta de uni&oacute;n</strong></h3>
					<p>{date}</p>
					<p>{employee_name}</p>
					<p>{address}</p>
					
					<p>Asunto: Nombramiento para el puesto de {designation}</p>
					
					<p>Estimado {employee_name},</p>
					
					<p>Nos complace ofrecerle el puesto de {designation} con {app_name}, la Compa&ntilde;&iacute;a en los siguientes t&eacute;rminos y</p>
					<p>condiciones:</p>
					
					<p>1. Comienzo del empleo</p>
					<p>Su empleo ser&aacute; efectivo a partir del {start_date}</p>
					
					<p>2. T&iacute;tulo del trabajo</p>
					<p>El t&iacute;tulo de su trabajo ser&aacute; {designation}.</p>
					
					<p>3. Salario</p>
					<p>Su salario y otros beneficios ser&aacute;n los establecidos en el Anexo 1 del presente.</p>
					
					<p>4. Lugar de destino</p>
					<p>Se le publicar&aacute; en {branch}. Sin embargo, es posible que deba trabajar en cualquier lugar de negocios que tenga la Compa&ntilde;&iacute;a, o</p>
					<p>puede adquirir posteriormente.</p>
					
					<p>5. Horas de trabajo</p>
					<p>Los d&iacute;as normales de trabajo son de lunes a viernes. Se le pedir&aacute; que trabaje las horas que sean necesarias para el</p>
					<p>cumplimiento adecuado de sus deberes para con la Compa&ntilde;&iacute;a. El horario normal de trabajo es de {start_time} a {end_time} y usted est&aacute;</p>
					<p>se espera que trabaje no menos de {total_hours} horas cada semana y, si es necesario, horas adicionales dependiendo de su</p>
					<p>responsabilidades.</p>
					
					<p>6. Licencia/Vacaciones</p>
					<p>6.1 Tiene derecho a un permiso eventual de 12 d&iacute;as.</p>
					<p>6.2 Tiene derecho a 12 d&iacute;as laborables de baja por enfermedad remunerada.</p>
					<p>6.3 La Compa&ntilde;&iacute;a deber&aacute; notificar una lista de d&iacute;as festivos declarados al comienzo de cada a&ntilde;o.</p>
					
					<p>7. Naturaleza de los deberes</p>
					<p>Desempe&ntilde;ar&aacute; lo mejor que pueda todas las funciones inherentes a su puesto y aquellas funciones adicionales que la empresa</p>
					<p>puede pedirte que act&uacute;es, de vez en cuando. Sus deberes espec&iacute;ficos se establecen en el Anexo II del presente.</p>
					
					<p>8. Propiedad de la empresa</p>
					<p>Siempre mantendr&aacute; en buenas condiciones la propiedad de la Compa&ntilde;&iacute;a, que se le puede confiar para uso oficial durante el curso de</p>
					<p>su empleo, y devolver&aacute; todos esos bienes a la Compa&ntilde;&iacute;a antes de renunciar a su cargo, en caso contrario, el costo</p>
					<p>de la misma ser&aacute; recuperada de usted por la Compa&ntilde;&iacute;a.</p>
					
					<p>9. Tomar prestado/aceptar regalos</p>
					<p>No pedir&aacute; prestado ni aceptar&aacute; dinero, obsequios, recompensas o compensaciones por sus ganancias personales o se colocar&aacute; de otra manera</p>
					<p>bajo obligaci&oacute;n pecuniaria a cualquier persona/cliente con quien pueda tener tratos oficiales.</p>
					
					<p>10. Terminaci&oacute;n</p>
					<p>10.1 Su nombramiento puede ser rescindido por la Compa&ntilde;&iacute;a, sin ning&uacute;n motivo, al darle no menos de [Aviso] meses antes</p>
					<p>aviso por escrito o salario en su lugar. Para los efectos de esta cl&aacute;usula, se entender&aacute; por salario el salario base.</p>
					<p>10.2 Puede rescindir su empleo con la Compa&ntilde;&iacute;a, sin ninguna causa, dando no menos de [Aviso al empleado]</p>
					<p>meses de preaviso o salario por el per&iacute;odo no ahorrado, remanente despu&eacute;s del ajuste de licencias pendientes, a la fecha.</p>
					<p>10.3 La Compa&ntilde;&iacute;a se reserva el derecho de rescindir su empleo sumariamente sin ning&uacute;n per&iacute;odo de preaviso o pago por rescisi&oacute;n</p>
					<p>si tiene motivos razonables para creer que usted es culpable de mala conducta o negligencia, o ha cometido una violaci&oacute;n fundamental de</p>
					<p>contrato, o causado cualquier p&eacute;rdida a la Compa&ntilde;&iacute;a.</p>
					<p>10.4 A la terminaci&oacute;n de su empleo por cualquier motivo, devolver&aacute; a la Compa&ntilde;&iacute;a todos los bienes; documentos, y</p>
					<p>papel, tanto en original como en copia del mismo, incluyendo cualquier muestra, literatura, contratos, registros, listas, dibujos, planos,</p>
					<p>cartas, notas, datos y similares; e Informaci&oacute;n confidencial, en su posesi&oacute;n o bajo su control en relaci&oacute;n con su</p>
					<p>empleo o a los asuntos comerciales de los clientes.</p>
					<p>11. Información confidencial</p>
					<p>11.1 Durante su empleo en la Compa&ntilde;&iacute;a, dedicar&aacute; todo su tiempo, atenci&oacute;n y habilidad lo mejor que pueda para</p>
					<p>son negocios. Usted no deber&aacute;, directa o indirectamente, comprometerse o asociarse con, estar conectado, interesado, empleado o</p>
					<p>tiempo o seguir cualquier curso de estudio, sin el permiso previo de la Compa&ntilde;&iacute;a. participar en cualquier otro negocio o</p>
					<p>actividades o cualquier otro puesto o trabajo a tiempo parcial o seguir cualquier curso de estudio, sin el permiso previo de la</p>
					<p>Compa&ntilde;&iacute;a.</p>
					<p>11.2 Siempre debe mantener el m&aacute;s alto grado de confidencialidad y mantener como confidenciales los registros, documentos y otros</p>
					<p>Informaci&oacute;n confidencial relacionada con el negocio de la Compa&ntilde;&iacute;a que usted pueda conocer o confiarle por cualquier medio</p>
					<p>y utilizar&aacute; dichos registros, documentos e informaci&oacute;n solo de manera debidamente autorizada en inter&eacute;s de la Compa&ntilde;&iacute;a. Para</p>
					<p>A los efectos de esta cláusula, "Información confidencial" significa información sobre el negocio de la Compa&ntilde;&iacute;a y el de sus clientes.</p>
					<p>que no está disponible para el público en general y que usted puede aprender en el curso de su empleo. Esto incluye,</p>
					<p>pero no se limita a, información relacionada con la organización, sus listas de clientes, políticas de empleo, personal e información</p>
					<p>sobre los productos de la Compa&ntilde;&iacute;a, procesos que incluyen ideas, conceptos, proyecciones, tecnología, manuales, dibujos, diseños,</p>
					<p>especificaciones, y todos los papeles, currículos, registros y otros documentos que contengan dicha Información confidencial.</p>
					<p>11.3 En ningún momento, sacará ninguna Información confidencial de la oficina sin permiso.</p>
					<p>11.4 Su deber de salvaguardar y no divulgar</p>
					<p>La Información confidencial sobrevivirá a la expiración o terminación de este Acuerdo y/o su empleo con la Compa&ntilde;&iacute;a.</p>
					<p>11.5 El incumplimiento de las condiciones de esta cláusula le hará pasible de despido sumario en virtud de la cláusula anterior además de cualquier</p>
					<p>otro recurso que la Compa&ntilde;&iacute;a pueda tener contra usted por ley.</p>
					<p>12. Avisos</p>
					<p>Usted puede enviar notificaciones a la Compa&ntilde;&iacute;a a su domicilio social. La Compa&ntilde;&iacute;a puede enviarle notificaciones a usted en</p>
					<p>la dirección indicada por usted en los registros oficiales.</p>
					<p>13. Aplicabilidad de la política de la empresa</p>
					<p>La Compa&ntilde;&iacute;a tendrá derecho a hacer declaraciones de política de vez en cuando relacionadas con asuntos como el derecho a licencia, maternidad</p>
					<p>licencia, beneficios de los empleados, horas de trabajo, políticas de transferencia, etc., y puede modificarlas de vez en cuando a su sola discreción.</p>
					<p>Todas estas decisiones políticas de la Compa&ntilde;&iacute;a serán vinculantes para usted y anularán este Acuerdo en esa medida.</p>
					<p>14. Ley aplicable/Jurisdicción</p>
					<p>Su empleo con la Compa&ntilde;&iacute;a está sujeto a las leyes del País. Todas las disputas estarán sujetas a la jurisdicción del Tribunal Superior</p>
					<p>Sólo Gujarat.</p>
					<p>15. Aceptación de nuestra oferta</p>
					<p>Por favor, confirme su aceptación de este Contrato de Empleo firmando y devolviendo el duplicado.</p>
					<p>Le damos la bienvenida y esperamos recibir su aceptación y trabajar con usted.</p>
					<p>Tuyo sinceramente,</p>
					<p>{app_name}</p>
					<p>{date}</p>''',
					'fr': '''<h3 style="text-align: center;">Lettre dadh&eacute;sion</h3>
					<p>{date}</p>
					<p>{employee_name}</p>
					<p>{address}</p>
					
					<p>Objet : Nomination pour le poste de {designation}</p>
					
					<p>Cher {employee_name},</p>
					
					<p>Nous sommes heureux de vous proposer le poste de {designation} avec {app_name} la "Soci&eacute;t&eacute;" selon les conditions suivantes et</p>
					<p>les conditions:</p>
					
					<p>1. Entr&eacute;e en fonction</p>
					<p>Votre emploi sera effectif &agrave; partir du {start_date}</p>
					
					<p>2. Intitul&eacute; du poste</p>
					<p>Votre titre de poste sera {designation}.</p>
					
					<p>3. Salaire</p>
					<p>Votre salaire et vos autres avantages seront tels quindiqu&eacute;s &agrave; l'annexe 1 ci-jointe.</p>
					
					<p>4. Lieu de d&eacute;tachement</p>
					<p>Vous serez affect&eacute; &agrave; {branch}. Vous pouvez cependant &ecirc;tre tenu de travailler dans n'importe quel lieu d'affaires que la Soci&eacute;t&eacute; a, ou</p>
					<p>pourra acqu&eacute;rir plus tard.</p>
					
					<p>5. Heures de travail</p>
					<p>Les jours ouvrables normaux sont du lundi au vendredi. Vous devrez travailler les heures n&eacute;cessaires &agrave; la</p>
					<p>lexercice correct de vos fonctions envers la Soci&eacute;t&eacute;. Les heures normales de travail vont de {start_time} &agrave; {end_time} et vous &ecirc;tes</p>
					<p>devriez travailler au moins {total_hours} heures par semaine, et si n&eacute;cessaire, des heures suppl&eacute;mentaires en fonction de votre</p>
					<p>responsabilit&eacute;s.</p>''',
					'he': '''<h3 style="text-align: center;">מכתב הצטרפות</h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>נושא: מינוי לתפקיד של {designation}</p>
							<p>{employee_name} היקר,</p>
							<p>אנו שמחים להציע לך את התפקיד של {designation} ב-{app_name} theCompany בתנאים הבאים ו</p>
							<p>תנאים:</p>
							<p>1. תחילת עבודה</p>
							<p>העסקה שלך תיכנס לתוקף, החל מ-{start_date}</p>
							<p>2. כותרת תפקיד</p>
							<p>כותרת המשרה שלך תהיה {designation}.</p>
							<p>3. משכורת</p>
							<p>השכר וההטבות האחרות שלך יהיו כמפורט בתוספת 1, להלן.</p>
							<p>4. מקום הפרסום</p>
							<p>תפרסם ב-{branch}. עם זאת, ייתכן שתידרש לעבוד בכל מקום עסק שיש לחברה, או</p>
							<p>עשוי מאוחר יותר לרכוש.</p>
							<p>5. שעות עבודה</p>
							<p>ימי העבודה הרגילים הם שני עד שישי. תידרש לעבוד במשך שעות הדרושות ל</p>
							<p>מילוי נאות של חובותיך כלפי החברה. שעות העבודה הרגילות הן מ-{start_time} עד {end_time} ואתה</p>
							<p>צפוי לעבוד לא פחות מ-{total_hours} שעות בכל שבוע, ובמידת הצורך לשעות נוספות בהתאם ל</p>
							<p>אחריות.</p>''',
					'it': '''<h3 style="text-align: center;">Lettera di adesione</h3>
							<p>{date}</p>
							<p>{employee_name}</p>
							<p>{address}</p>
							<p>Oggetto: Appuntamento alla carica di {designation}</p>
							<p>Gentile {employee_name},</p>
							<p>Siamo lieti di offrirti la posizione di {designation} con {app_name} la "Societ&agrave;" alle seguenti condizioni e</p>
							<p>condizioni:</p>
							<p>1. Inizio del rapporto di lavoro</p>
							<p>Il tuo impiego sar&agrave; effettivo a partire da {start_date}</p>
							<p>2. Titolo di lavoro</p>
							<p>Il tuo titolo di lavoro sar&agrave; {designation}.</p>
							<p>3. Stipendio</p>
							<p>Il tuo stipendio e altri benefici saranno come indicato nell'Allegato 1, qui di seguito.</p>
							<p>4. Luogo di invio</p>
							<p>Sarai inviato a {branch}. Tuttavia, potrebbe essere richiesto di lavorare in qualsiasi luogo di attivit&agrave; che la Societ&agrave; ha, o</p>
							<p>potr&agrave; successivamente acquisire.</p>
							<p>5. Orario di lavoro</p>
							<p>I normali giorni lavorativi sono dal luned&igrave; al venerd&igrave;. Ti verr&agrave; richiesto di lavorare per le ore necessarie per il</p>
							<p>corretto adempimento dei propri doveri nei confronti della Societ&agrave;. L'orario di lavoro normale va da {start_time} a {end_time} e tu lo sei</p>
							<p>dovresti lavorare non meno di {total_hours} ore ogni settimana e, se necessario, per ore aggiuntive a seconda del tuo</p>
							<p>responsabilit&agrave;.</p>
							<p>6. Permessi/Festivit&agrave;</p>
							<p>6.1 Hai diritto a un congedo occasionale di 12 giorni.</p>
							<p>6.2 Hai diritto a 12 giorni lavorativi di congedo per malattia retribuito.</p>
							<p>6.3 La Societ&agrave; comunica all'inizio di ogni anno un elenco delle festivit&agrave; dichiarate.</p>
							<p>7. Natura degli incarichi</p>
							<p>Eseguirai al meglio delle tue capacit&agrave; tutti i compiti inerenti al tuo incarico e compiti aggiuntivi come l'azienda</p>
							<p>pu&ograve; invitarti a esibirti, di tanto in tanto. I tuoi doveri specifici sono stabiliti nell'Allegato II del presente documento.</p>
							<p>8. Propriet&agrave; aziendale</p>
							<p>Manterrai sempre in buono stato i beni dell'Azienda, che nel corso dell'anno potrebbero esserti affidati per uso ufficiale</p>
							<p>del tuo impiego, e restituirai tutte queste propriet&agrave; alla Societ&agrave; prima della rinuncia al tuo addebito, in caso contrario il costo</p>
							<p>degli stessi saranno da voi recuperati dalla Societ&agrave;.</p>
							<p>9. Prendere in prestito/accettare regali</p>
							<p>Non prenderai in prestito n&eacute; accetterai denaro, dono, ricompensa o compenso per i tuoi guadagni personali da o altrimenti collocato te stesso</p>
							<p>sotto obbligazione pecuniaria nei confronti di qualsiasi persona/cliente con cui potresti avere rapporti ufficiali.</p>
							<p>10. Cessazione</p>
							<p>10.1 Il tuo incarico pu&ograve; essere risolto dalla Societ&agrave;, senza alcun motivo, dandoti non meno di [Avviso] mesi prima</p>
							<p>avviso scritto o stipendio in sostituzione di esso. Ai fini della presente clausola, per stipendio si intende lo stipendio base.</p>
							<p>10.2 &Egrave; possibile terminare il proprio rapporto di lavoro con la Societ&agrave;, senza alcuna causa, fornendo non meno di [Avviso per il dipendente]</p>
							<p>mesi di preavviso o stipendio per il periodo non risparmiato, lasciato dopo l'adeguamento delle ferie pendenti, come alla data.</p>
							<p>10.3 La Societ&agrave; si riserva il diritto di terminare il rapporto di lavoro sommariamente senza alcun periodo di preavviso o pagamento di cessazione</p>
							<p>se ha fondati motivi per ritenere che tu sia colpevole di cattiva condotta o negligenza, o abbia commesso una violazione fondamentale</p>
							<p>del contratto, o ha causato danni alla Societ&agrave;.</p>
							<p>10.4 Alla cessazione del rapporto di lavoro per qualsiasi motivo, restituirete alla Societ&agrave; tutti i beni; documenti, e</p>
							<p>carta, sia in originale che in copia, inclusi eventuali campioni, letteratura, contratti, registrazioni, elenchi, disegni, progetti,</p>
							<p>lettere, note, dati e simili; e Informazioni Riservate, in tuo possesso o sotto il tuo controllo, relative alla tua</p>
							<p>lavoro o agli affari dei clienti.</p>
							<p>11. Confidential Information</p>
							<p>11.1 During your employment with the Company you will devote your whole time, attention, and skill to the best of your ability for</p>
							<p>its business. You shall not, directly or indirectly, engage or associate yourself with, be connected with, concerned, employed, or</p>
							<p>time or pursue any course of study whatsoever, without the prior permission of the Company.engaged in any other business or</p>
							<p>activities or any other post or work part-time or pursue any course of study whatsoever, without the prior permission of the</p>
							<p>Company.</p>
							<p>11.2 You must always maintain the highest degree of confidentiality and keep as confidential the records, documents, and other&nbsp;</p>
							<p>Confidential Information relating to the business of the Company which may be known to you or confided in you by any means</p>
							<p>and you will use such records, documents and information only in a duly authorized manner in the interest of the Company. For</p>
							<p>the purposes of this clause &lsquo;Confidential Information&rsquo; means information about the Company&rsquo;s business and that of its customers</p>
							<p>which is not available to the general public and which may be learned by you in the course of your employment. This includes,</p>
							<p>but is not limited to, information relating to the organization, its customer lists, employment policies, personnel, and information</p>
							<p>about the Company&rsquo;s products, processes including ideas, concepts, projections, technology, manuals, drawing, designs,&nbsp;</p>
							<p>specifications, and all papers, resumes, records and other documents containing such Confidential Information.</p>
							<p>11.3 At no time, will you remove any Confidential Information from the office without permission.</p>
							<p>11.4 Your duty to safeguard and not disclos</p>
							<p>e Confidential Information will survive the expiration or termination of this Agreement and/or your employment with the Company.</p>
							<p>11.5 Breach of the conditions of this clause will render you liable to summary dismissal under the clause above in addition to any</p>
							<p>other remedy the Company may have against you in law.</p>''',
					'ja': '''<h3 style="text-align: center;">入会の手紙</h3>
					<p>{date}</p>
					<p>{employee_name}</p>
					<p>{address}</p>
					<p>件名: {designation} の役職への任命</p>
					<p>{employee_name} 様</p>
					<p>{app_name} の {designation} の地位を以下の条件で「会社」として提供できることをうれしく思います。</p>
					<p>条件：</p>
					<p>1. 雇用開始</p>
					<p>あなたの雇用は {start_date} から有効になります</p>
					<p>2. 役職</p>
					<p>あなたの役職は{designation}になります。</p>
					<p>3. 給与</p>
					<p>あなたの給与およびその他の福利厚生は、本明細書のスケジュール 1 に記載されているとおりです。</p>
					<p>4. 掲示場所</p>
					<p>{branch} に掲載されます。ただし、会社が所有する事業所で働く必要がある場合があります。</p>
					<p>後で取得する場合があります。</p>
					<p>5. 労働時間</p>
					<p>通常の営業日は月曜日から金曜日です。あなたは、そのために必要な時間働く必要があります。</p>
					<p>会社に対するあなたの義務の適切な遂行。通常の勤務時間は {start_time} から {end_time} までで、あなたは</p>
					<p>毎週 {total_hours} 時間以上の勤務が期待される</p>
					<p>責任。</p>''',
					'nl': '''<h3 style="text-align: center;">Deelnemende brief</h3>
					<p>{date}</p>
					<p>{employee}</p>
					<p>{address}</p>
					<p>Onderwerp: Benoeming voor de functie van {designation}</p>
					<p>Beste {employee_name},</p>
					<p>We zijn verheugd u de positie van {designation} bij {app_name} het Bedrijf aan te bieden onder de volgende voorwaarden en</p>
					<p>conditie:</p>
					<p>1. Indiensttreding</p>
					<p>Uw dienstverband gaat in op {start_date}</p>
					<p>2. Functietitel</p>
					<p>Uw functietitel wordt {designation}.</p>
					<p>3. Salaris</p>
					<p>Uw salaris en andere voordelen zijn zoals uiteengezet in Schema 1 hierbij.</p>
					<p>4. Plaats van detachering</p>
					<p>Je wordt geplaatst op {branch}. Het kan echter zijn dat u moet werken op een bedrijfslocatie die het Bedrijf heeft, of</p>
					<p>later kan verwerven.</p>
					<p>5. Werkuren</p>
					<p>De normale werkdagen zijn van maandag tot en met vrijdag. Je zal de uren moeten werken die nodig zijn voor de</p>
					<p>correcte uitvoering van uw taken jegens het bedrijf. De normale werkuren zijn van {start_time} tot {end_time} en jij bent</p>
					<p>naar verwachting niet minder dan {total_hours} uur per week werken, en indien nodig voor extra uren, afhankelijk van uw</p>
					<p>verantwoordelijkheden.</p>''',
					'pl': '''<h3 style="text-align: center;">Dołączanie listu</h3>
							<p>{date }</p>
							<p>{employee_name }</p>
							<p>{address }</p>
							<p>Dotyczy: mianowania na stanowisko {designation}</p>
							<p>Szanowny {employee_name },</p>
							<p>Mamy przyjemność zaoferować Państwu, stanowisko {designation} z {app_name } "Sp&oacute;łka" na poniższych warunkach i</p>
							<p>warunki:</p>
							<p>1. Rozpoczęcie pracy</p>
							<p>Twoje zatrudnienie będzie skuteczne, jak na {start_date }</p>
							<p>2. Tytuł zadania</p>
							<p>Tw&oacute;j tytuł pracy to {designation}.</p>
							<p>3. Salary</p>
							<p>Twoje wynagrodzenie i inne świadczenia będą określone w Zestawieniu 1, do niniejszego rozporządzenia.</p>
							<p>4. Miejsce delegowania</p>
							<p>Użytkownik zostanie opublikowany w {branch }. Użytkownik może jednak być zobowiązany do pracy w dowolnym miejscu prowadzenia działalności, kt&oacute;re Sp&oacute;łka posiada, lub może p&oacute;źniej nabyć.</p>
							<p>5. Godziny pracy</p>
							<p>Normalne dni robocze są od poniedziałku do piątku. Będziesz zobowiązany do pracy na takie godziny, jakie są niezbędne do prawidłowego wywiązania się ze swoich obowiązk&oacute;w wobec Sp&oacute;łki. Normalne godziny pracy to {start_time } do {end_time }, a użytkownik oczekuje, że będzie pracować nie mniej niż {total_hours } godzin tygodniowo, a jeśli to konieczne, przez dodatkowe godziny w zależności od Twojego</p>
							<p>odpowiedzialności.</p>''',
					'pt': '''<h3 style="text-align: center;">Carta De Ades&atilde;o</h3>
					<p>{data}</p>
					<p>{employee_name}</p>
					<p>{address}</p>
					<p>Assunto: Nomea&ccedil;&atilde;o para o cargo de {designation}</p>
					<p>Querido {employee_name},</p>
					<p>Temos o prazer de oferec&ecirc;-lo, a posi&ccedil;&atilde;o de {designation} com {app_name} a Empresa nos seguintes termos e</p>
					<p>condi&ccedil;&otilde;es:</p>
					<p>1. Comentamento do emprego</p>
					<p>Seu emprego ser&aacute; efetivo, a partir de {start_date}</p>
					<p>2. T&iacute;tulo do emprego</p>
					<p>Seu cargo de trabalho ser&aacute; {designation}.</p>
					<p>3. Sal&aacute;rio</p>
					<p>Seu sal&aacute;rio e outros benef&iacute;cios ser&atilde;o conforme estabelecido no Planejamento 1, hereto.</p>
					<p>4. Local de postagem</p>
					<p>Voc&ecirc; ser&aacute; postado em {branch}. Voc&ecirc; pode, no entanto, ser obrigado a trabalhar em qualquer local de neg&oacute;cios que a Empresa tenha, ou possa posteriormente adquirir.</p>
					<p>5. Horas de Trabalho</p>
					<p>Os dias normais de trabalho s&atilde;o de segunda a sexta-feira. Voc&ecirc; ser&aacute; obrigado a trabalhar por tais horas, conforme necess&aacute;rio para a quita&ccedil;&atilde;o adequada de suas fun&ccedil;&otilde;es para a Companhia. As horas de trabalho normais s&atilde;o de {start_time} para {end_time} e voc&ecirc; deve trabalhar n&atilde;o menos de {total_horas} horas semanais, e se necess&aacute;rio para horas adicionais dependendo do seu</p>
					<p>responsabilidades.</p>
					<p>6. Leave / Holidays</p>
					<p>6,1 Voc&ecirc; tem direito a licen&ccedil;a casual de 12 dias.</p>
					<p>6,2 Voc&ecirc; tem direito a 12 dias &uacute;teis de licen&ccedil;a remunerada remunerada.</p>
					<p>6,3 Companhia notificar&aacute; uma lista de feriados declarados no in&iacute;cio de cada ano.&nbsp;</p>''',
					'ru': '''<h3 style="text-align: center;">Присоединение к письму</h3>
					<p>{date}</p>
					<p>{ employee_name }</p>
					<p>{address}</p>
					<p>Тема: Назначение на должность {designation}</p>
					<p>Уважаемый { employee_name },</p>
					<p>Мы рады предложить Вам, позицию {designation} с { app_name } Компания на следующих условиях и</p>
					<p>условия:</p>
					<p>1. Начало работы</p>
					<p>Ваше трудоустройство будет эффективным, начиная с { start_date }</p>
					<p>2. Название должности</p>
					<p>Ваш заголовок задания будет {designation}.</p>
					<p>3. Зарплата</p>
					<p>Ваши оклады и другие пособия будут установлены в соответствии с расписанием, изложенным в приложении 1 к настоящему.</p>
					<p>4. Место размещения</p>
					<p>Вы будете работать в { branch }. Вы, однако, можете работать в любом месте, которое компания имеет или может впоследствии приобрести.</p>
					<p>5. Часы работы</p>
					<p>Обычные рабочие дни — с понедельника по пятницу. Вы должны будете работать в течение таких часов, как это необходимо для надлежащего выполнения Ваших обязанностей перед компанией. Обычные рабочие часы — от { start_time } до { end_time }, и вы, как ожидается, будете работать не менее { total_hours } часов каждую неделю, и при необходимости — дополнительные часы в зависимости от вашего</p>
					<p>ответственности.</p>''',
					'tr': '''<h3 style="text-align: center;">Katılım Mektubu</h3>
					<p>{tarih}</p>
					<p>{çalışan_adı}</p>
					<p>{adres</p>
					<p>Konu: {tanımlama</p> görevi için randevu
					<p>Sayın {çalışan_adı},</p>
					<p>Aşağıdaki şartlar ve</p> ile Şirkette {app_name} ile {designation} konumunu size sunmaktan mutluluk duyuyoruz.</p>
					<p>koşullar:</p>
					<p>1. İşe başlama</p>
					<p>İstihdamınız {start_date}</p> itibarıyla geçerli olacak
					<p>2. İş unvanı</p>
					<p>İş unvanınız {tanımlama} olacaktır.</p>
					<p>3. maaş</p>
					<p>Maaşınız ve diğer yardımlarınız, bundan böyle Çizelge 1 de belirtildiği gibi olacaktır.</p>
					<p>4. Gönderim yeri</p>
					<p>{branch} adresinde ilan edileceksiniz. Ancak, Şirketin sahip olduğu herhangi bir işyerinde çalışmanız gerekebilir veya</p>
					<p>daha sonra edinilebilir.</p>
					<p>5. Çalışma Saatleri</p>
					<p>Normal çalışma günleri Pazartesi den Cuma ya kadardır. </p> için gerekli olan saatlerde çalışmanız istenecektir.
					<p>Şirkete karşı görevlerinizi uygun şekilde yerine getirme. Normal çalışma saatleri {start_time} ile {end_time} arasındadır ve siz</p>
					<p>Her hafta en az {total_hours} saat ve gerekirse sizin durumunuza bağlı olarak ek saat çalışması bekleniyor</p>
					<p>sorumluluklar.</p>''',
					'pt-br': '''<h3 style="text-align: center;">Carta De Ades&atilde;o</h3>
						<p>{data}</p>
						<p>{employee_name}</p>
						<p>{address}</p>
						<p>Assunto: Nomea&ccedil;&atilde;o para o cargo de {designation}</p>
						<p>Querido {employee_name},</p>
						<p>Temos o prazer de oferec&ecirc;-lo, a posi&ccedil;&atilde;o de {designation} com {app_name} a Empresa nos seguintes termos e</p>
						<p>condi&ccedil;&otilde;es:</p>
						<p>1. Comentamento do emprego</p>
						<p>Seu emprego ser&aacute; efetivo, a partir de {start_date}</p>
						<p>2. T&iacute;tulo do emprego</p>
						<p>Seu cargo de trabalho ser&aacute; {designation}.</p>
						<p>3. Sal&aacute;rio</p>
						<p>Seu sal&aacute;rio e outros benef&iacute;cios ser&atilde;o conforme estabelecido no Planejamento 1, hereto.</p>
						<p>4. Local de postagem</p>
						<p>Voc&ecirc; ser&aacute; postado em {branch}. Voc&ecirc; pode, no entanto, ser obrigado a trabalhar em qualquer local de neg&oacute;cios que a Empresa tenha, ou possa posteriormente adquirir.</p>
						<p>5. Horas de Trabalho</p>
						<p>Os dias normais de trabalho s&atilde;o de segunda a sexta-feira. Voc&ecirc; ser&aacute; obrigado a trabalhar por tais horas, conforme necess&aacute;rio para a quita&ccedil;&atilde;o adequada de suas fun&ccedil;&otilde;es para a Companhia. As horas de trabalho normais s&atilde;o de {start_time} para {end_time} e voc&ecirc; deve trabalhar n&atilde;o menos de {total_horas} horas semanais, e se necess&aacute;rio para horas adicionais dependendo do seu</p>
						<p>responsabilidades.</p>'''
        }
        for lang, content in default_template.items():
            JoiningLetter.objects.create(
                lang=lang,
                content=content,
                created_by=user_id
            )
