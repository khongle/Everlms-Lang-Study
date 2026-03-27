<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* themes/custom/lms_classic/templates/lms-homepage.html.twig */
class __TwigTemplate_1f50de396d7ca2ff4f6dca822f6f44e1 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 2
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("lms_classic/global"), "html", null, true);
        yield "
";
        // line 3
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("lms_classic/homepage"), "html", null, true);
        yield "

";
        // line 6
        yield "<section class=\"lms-hero\">
  <div class=\"container lms-hero__container\">
    <div class=\"row align-items-center\">
      <div class=\"col-lg-6 lms-hero__content-col\" data-aos=\"fade-right\">
        <div class=\"lms-hero__badge\">";
        // line 10
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("🏛️ Classic Elegance"));
        yield "</div>
        <h1 class=\"lms-hero__title\">
          ";
        // line 12
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Master Your Future With"));
        yield "<br>
          <span class=\"lms-text-gold\">";
        // line 13
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("EverLMS Platform"));
        yield "</span>
        </h1>
        <p class=\"lms-hero__subtitle\">
          ";
        // line 16
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Discover premium online courses designed to accelerate your career. Learn from industry experts and achieve your goals faster with our prestigious platform."));
        yield "
        </p>

        ";
        // line 20
        yield "        <div class=\"lms-hero__search\">
          <form action=\"";
        // line 21
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.catalog"));
        yield "\" method=\"get\">
            <div class=\"lms-search-bar\">
              <i class=\"fas fa-search lms-search-icon\"></i>
              <input type=\"text\" name=\"search\" placeholder=\"";
        // line 24
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Search our library..."));
        yield "\" class=\"lms-search-input\">
              <button type=\"submit\" class=\"lms-btn\">
                ";
        // line 26
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Search"));
        yield "
              </button>
            </div>
          </form>
        </div>
        
        ";
        // line 33
        yield "        <div class=\"lms-hero__stats\" data-aos=\"fade-up\" data-aos-delay=\"200\">
          <div class=\"lms-stat-item\">
            <h3 class=\"lms-stat-number\">";
        // line 35
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "courses", [], "any", true, true, true, 35)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "courses", [], "any", false, false, true, 35), "100+")) : ("100+")), "html", null, true);
        yield "</h3>
            <span class=\"lms-stat-label\">";
        // line 36
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar("Premium<br>Courses");
        yield "</span>
          </div>
          <div class=\"lms-stat-divider\"></div>
          <div class=\"lms-stat-item\">
            <h3 class=\"lms-stat-number\">";
        // line 40
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "students", [], "any", true, true, true, 40)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "students", [], "any", false, false, true, 40), "10k+")) : ("10k+")), "html", null, true);
        yield "</h3>
            <span class=\"lms-stat-label\">";
        // line 41
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar("Enrolled<br>Students");
        yield "</span>
          </div>
          <div class=\"lms-stat-divider\"></div>
          <div class=\"lms-stat-item\">
            <h3 class=\"lms-stat-number\">";
        // line 45
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "satisfaction", [], "any", true, true, true, 45)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["stats"] ?? null), "satisfaction", [], "any", false, false, true, 45), "99%")) : ("99%")), "html", null, true);
        yield "</h3>
            <span class=\"lms-stat-label\">";
        // line 46
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar("Satisfaction<br>Rate");
        yield "</span>
          </div>
        </div>
      </div>
      
      <div class=\"col-lg-6 lms-hero__image-col\" data-aos=\"fade-left\" data-aos-delay=\"100\">
        <div class=\"lms-hero__image-wrapper\">
          <img src=\"https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80\" alt=\"Prestigious University\" class=\"lms-hero-img\">
          <div class=\"lms-hero__floating-card\">
            <div class=\"lms-floating-icon\"><i class=\"fas fa-university\"></i></div>
            <div class=\"lms-floating-text\">
              <strong>";
        // line 57
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Ivy League Quality"));
        yield "</strong>
              <span>";
        // line 58
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Academic Excellence"));
        yield "</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

";
        // line 68
        if ((($context["logged_in"] ?? null) && (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["my_courses"] ?? null)) > 0))) {
            // line 69
            yield "<section class=\"lms-section lms-section--my-learning\" data-aos=\"fade-up\" style=\"background:var(--lms-bg-cream);\">
  <div class=\"container\">
    <div class=\"lms-section-header\">
      <h2 class=\"lms-section-title\"><i class=\"fas fa-book-reader\" style=\"color:var(--lms-primary-burgundy);\"></i> ";
            // line 72
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Continue Learning"));
            yield "</h2>
      <a href=\"";
            // line 73
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::default($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_progress.dashboard"), "#"), "html", null, true);
            yield "\" class=\"lms-link-more\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("My Dashboard"));
            yield " <i class=\"fas fa-arrow-right\"></i></a>
    </div>
    <div class=\"lms-progress-cards\" style=\"display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:20px;\">
      ";
            // line 76
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["my_courses"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                // line 77
                yield "        <div class=\"lms-progress-card lms-classic-card\">
          <div class=\"lms-progress-card__thumb\" style=\"height:150px; overflow:hidden;\">
            ";
                // line 79
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "course", [], "any", false, false, true, 79), "thumbnail_url", [], "any", false, false, true, 79)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 80
                    yield "              <img src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "course", [], "any", false, false, true, 80), "thumbnail_url", [], "any", false, false, true, 80), "html", null, true);
                    yield "\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "course", [], "any", false, false, true, 80), "name", [], "any", false, false, true, 80), "html", null, true);
                    yield "\" style=\"width:100%; height:100%; object-fit:cover;\">
            ";
                } else {
                    // line 82
                    yield "              <div class=\"lms-thumb-placeholder\" style=\"background:#e0dcd3; height:100%; display:flex; align-items:center; justify-content:center;\"><i class=\"fas fa-video\"></i></div>
            ";
                }
                // line 84
                yield "          </div>
          <div class=\"lms-progress-card__info\" style=\"padding:15px;\">
            <h4 class=\"lms-course-title\" style=\"color:var(--lms-primary-navy); margin:0 0 15px 0;\">";
                // line 86
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "course", [], "any", false, false, true, 86), "name", [], "any", false, false, true, 86), "html", null, true);
                yield "</h4>
            <div class=\"lms-progress-wrapper\">
              <div class=\"lms-progress-bar\" style=\"background:rgba(0,0,0,0.1); height:8px; border-radius:4px; overflow:hidden; margin-bottom:5px;\">
                <div class=\"lms-progress-fill\" style=\"background:var(--lms-accent-gold); height:100%; width: ";
                // line 89
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "progress", [], "any", false, false, true, 89), "html", null, true);
                yield "%;\"></div>
              </div>
              <span class=\"lms-progress-text\" style=\"color:var(--lms-text-muted); font-size:0.85rem;\">";
                // line 91
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "progress", [], "any", false, false, true, 91), "html", null, true);
                yield "% ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Completed"));
                yield "</span>
            </div>
          </div>
          <div class=\"lms-progress-card__action\" style=\"padding:15px; border-top:1px solid rgba(0,0,0,0.05);\">
            <a href=\"";
                // line 95
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "course", [], "any", false, false, true, 95), "continue_url", [], "any", false, false, true, 95), "html", null, true);
                yield "\" class=\"lms-btn lms-btn--outline\" style=\"width:100%; padding:8px;\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Resume"));
                yield "</a>
          </div>
        </div>
      ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['item'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 99
            yield "    </div>
  </div>
</section>
";
        }
        // line 103
        yield "
";
        // line 105
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["categories"] ?? null)) > 0)) {
            // line 106
            yield "<section class=\"lms-section lms-bg-darker\" data-aos=\"fade-up\" style=\"background:var(--lms-bg-dark-cream);\">
  <div class=\"container\">
    <div class=\"lms-section-header text-center\">
      <h2 class=\"lms-section-title\">";
            // line 109
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Explore Popular Fields"));
            yield "</h2>
      <p class=\"lms-section-subtitle\">";
            // line 110
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Find the perfect topic to boost your skills and career"));
            yield "</p>
    </div>
    <div class=\"lms-categories-grid\">
      ";
            // line 113
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["categories"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["cat"]) {
                // line 114
                yield "        <a href=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::default($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.category_detail", ["taxonomy_term" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "entity", [], "any", false, false, true, 114), "id", [], "any", false, false, true, 114)]), "#"), "html", null, true);
                yield "\" class=\"lms-category-card lms-classic-card\" style=\"text-align: center; display: flex; flex-direction: column; align-items: center; padding: 40px 20px;\">
          <div class=\"lms-category__large-icon\" style=\"width: 120px; height: 120px; margin: 0 auto 20px auto; border-radius: 50%; overflow: hidden; border: 4px solid #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); background: var(--lms-primary-navy); display: flex; justify-content: center; align-items: center; flex-shrink: 0;\">
            ";
                // line 116
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "thumbnail_url", [], "any", false, false, true, 116)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 117
                    yield "              <img src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "thumbnail_url", [], "any", false, false, true, 117), "html", null, true);
                    yield "\" alt=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "name", [], "any", false, false, true, 117), "html", null, true);
                    yield "\" style=\"width: 100%; height: 100%; object-fit: cover;\">
            ";
                } else {
                    // line 119
                    yield "              <i class=\"fas fa-layer-group\" style=\"font-size: 3rem; color: var(--lms-accent-gold);\"></i>
            ";
                }
                // line 121
                yield "          </div>
          <div class=\"lms-category-card__content\" style=\"width: 100%;\">
            <h3 class=\"lms-category-card__title\" style=\"margin-bottom: 15px; font-size: 1.35rem; line-height: 1.4;\">";
                // line 123
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "name", [], "any", false, false, true, 123), "html", null, true);
                yield "</h3>
            <p class=\"lms-category-card__desc\" style=\"margin-bottom: 25px; line-height: 1.6;\">";
                // line 124
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::slice($this->env->getCharset(), Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "description", [], "any", false, false, true, 124)), 0, 80), "html", null, true);
                yield "...</p>
            <div class=\"lms-category-card__meta\" style=\"justify-content: center; margin-top: auto;\">
              <span class=\"lms-badge\" style=\"background:var(--lms-accent-gold); color:var(--lms-primary-navy); font-weight:700; border-radius:2px; font-family:var(--font-heading); font-size: 0.9rem; padding: 6px 16px;\">";
                // line 126
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "course_count", [], "any", false, false, true, 126), "html", null, true);
                yield " ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Courses"));
                yield "</span>
            </div>
          </div>
        </a>
      ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['cat'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 131
            yield "    </div>
  </div>
</section>
";
        }
        // line 135
        yield "
";
        // line 137
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["featured_courses"] ?? null)) > 0)) {
            // line 138
            yield "<section class=\"lms-section\" data-aos=\"fade-up\" style=\"background:var(--lms-bg-cream);\">
  <div class=\"container\">
    <div class=\"lms-section-header\">
      <h2 class=\"lms-section-title\">";
            // line 141
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Featured Courses"));
            yield "</h2>
      <a href=\"";
            // line 142
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.catalog"));
            yield "\" class=\"lms-link-more\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("View All Courses"));
            yield " <i class=\"fas fa-arrow-right\"></i></a>
    </div>
    <div class=\"grid-courses lms-courses-grid\">
      ";
            // line 145
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["featured_courses"] ?? null));
            $context['loop'] = [
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            ];
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["card"]) {
                // line 146
                yield "        <div class=\"lms-course-wrapper\" data-aos=\"fade-up\" data-aos-delay=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "index0", [], "any", false, false, true, 146) * 100), "html", null, true);
                yield "\">
          ";
                // line 147
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ["#theme" => "lms_course_card", "#card" => $context["card"]], "html", null, true);
                yield "
        </div>
      ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['card'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 150
            yield "    </div>
  </div>
</section>
";
        }
        // line 154
        yield "
";
        // line 156
        yield "<section class=\"lms-section lms-bg-darker\" data-aos=\"fade-up\" style=\"background:var(--lms-bg-dark-cream);\">
  <div class=\"container\">
    <div class=\"lms-section-header text-center\" style=\"margin-bottom: 50px;\">
      <h2 class=\"lms-section-title\">";
        // line 159
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Why Choose EverLMS"));
        yield "</h2>
    </div>
    <div class=\"lms-values-grid\">
      ";
        // line 162
        $context["values"] = [["icon" => "fa-university", "title" => t("Prestigious Education"), "desc" => t("Experience Ivy-League quality structure and rigorous curriculum standards.")], ["icon" => "fa-user-tie", "title" => t("Expert Scholars"), "desc" => t("Gain insights from vetted industry professionals and leading academics.")], ["icon" => "fa-award", "title" => t("Certified Excellence"), "desc" => t("Earn verified certificates that carry weight in the professional world.")], ["icon" => "fa-book-open", "title" => t("Rich Library"), "desc" => t("A massive archive of academic materials and premium exclusive content.")]];
        // line 168
        yield "      ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["values"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["val"]) {
            // line 169
            yield "        <div class=\"lms-value-card lms-classic-card\">
          <div class=\"lms-value-card__icon\" style=\"background:transparent; border:1px solid rgba(0,0,0,0.1);\">
            <i class=\"fas ";
            // line 171
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["val"], "icon", [], "any", false, false, true, 171), "html", null, true);
            yield "\" style=\"color:var(--lms-primary-burgundy);\"></i>
          </div>
          <h3>";
            // line 173
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["val"], "title", [], "any", false, false, true, 173), "html", null, true);
            yield "</h3>
          <p>";
            // line 174
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["val"], "desc", [], "any", false, false, true, 174), "html", null, true);
            yield "</p>
        </div>
      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['val'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 177
        yield "    </div>
  </div>
</section>

";
        // line 182
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["testimonials"] ?? null)) > 0)) {
            // line 183
            yield "<section class=\"lms-section\" data-aos=\"fade-up\" style=\"background:var(--lms-bg-cream);\">
  <div class=\"container\">
    <div class=\"lms-section-header text-center\">
      <h2 class=\"lms-section-title\">";
            // line 186
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Alumni Voices"));
            yield "</h2>
    </div>
    <div class=\"lms-testimonials-carousel lms-values-grid\">
      ";
            // line 189
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["testimonials"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["tm"]) {
                // line 190
                yield "        <div class=\"lms-testimonial-card lms-classic-card\" style=\"padding:30px; display:flex; flex-direction:column; justify-content:space-between;\">
          <div>
            <div class=\"lms-testimonial-card__rating\" style=\"margin-bottom:15px;\">
              ";
                // line 193
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(range(1, CoreExtension::getAttribute($this->env, $this->source, $context["tm"], "rating", [], "any", false, false, true, 193)));
                foreach ($context['_seq'] as $context["_key"] => $context["i"]) {
                    // line 194
                    yield "                <i class=\"fas fa-star\" style=\"color:var(--lms-accent-gold);\"></i>
              ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['i'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 196
                yield "            </div>
            <p class=\"lms-testimonial-card__content\" style=\"font-family:var(--font-body); font-style:italic; font-size:1.05rem; color:var(--lms-text-dark);\">\"";
                // line 197
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["tm"], "content", [], "any", false, false, true, 197), "html", null, true);
                yield "\"</p>
          </div>
          <div class=\"lms-testimonial-card__author\" style=\"display:flex; gap:15px; align-items:center; margin-top:20px; border-top:1px solid rgba(0,0,0,0.1); padding-top:20px;\">
            <img src=\"";
                // line 200
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["tm"], "avatar", [], "any", false, false, true, 200), "html", null, true);
                yield "\" alt=\"";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["tm"], "name", [], "any", false, false, true, 200), "html", null, true);
                yield "\" class=\"lms-author-avatar\" style=\"width:50px; height:50px; border-radius:50%; object-fit:cover;\">
            <div class=\"lms-author-info\">
              <h4 class=\"lms-author-name\" style=\"margin:0; color:var(--lms-primary-navy); font-family:var(--font-heading);\">";
                // line 202
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["tm"], "name", [], "any", false, false, true, 202), "html", null, true);
                yield "</h4>
              <span class=\"lms-author-role\" style=\"font-size:0.85rem; color:var(--lms-text-muted);\">";
                // line 203
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["tm"], "role", [], "any", false, false, true, 203), "html", null, true);
                yield "</span>
            </div>
          </div>
        </div>
      ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['tm'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 208
            yield "    </div>
  </div>
</section>
";
        }
        // line 212
        yield "
";
        // line 214
        yield "<section class=\"lms-section\" data-aos=\"zoom-in\" style=\"margin-bottom:80px; padding-top:40px;\">
  <div class=\"container\">
    <div class=\"lms-classic-card\" style=\"padding: 60px; text-align: center; border-top: 4px solid var(--lms-accent-gold);\">
      <h2 style=\"font-family: var(--font-heading); color: var(--lms-primary-navy); margin-bottom: 20px; font-size: 2.5rem;\">";
        // line 217
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Ready to Begin Your Journey?"));
        yield "</h2>
      <p style=\"color: var(--lms-text-muted); font-size: 1.15rem; margin-bottom: 40px;\">";
        // line 218
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Join thousands of scholars and elevate your learning experience today."));
        yield "</p>
      <div class=\"lms-cta-actions\">
        <a href=\"";
        // line 220
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::default($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("user.register"), "#"), "html", null, true);
        yield "\" class=\"lms-btn\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Apply Now"));
        yield "</a>
        <a href=\"";
        // line 221
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_course.catalog"));
        yield "\" class=\"lms-btn lms-btn--outline\" style=\"margin-left: 15px;\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Browse Library"));
        yield "</a>
      </div>
    </div>
  </div>
</section>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["stats", "logged_in", "my_courses", "categories", "featured_courses", "loop", "testimonials"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/lms_classic/templates/lms-homepage.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  531 => 221,  525 => 220,  520 => 218,  516 => 217,  511 => 214,  508 => 212,  502 => 208,  491 => 203,  487 => 202,  480 => 200,  474 => 197,  471 => 196,  464 => 194,  460 => 193,  455 => 190,  451 => 189,  445 => 186,  440 => 183,  438 => 182,  432 => 177,  423 => 174,  419 => 173,  414 => 171,  410 => 169,  405 => 168,  403 => 162,  397 => 159,  392 => 156,  389 => 154,  383 => 150,  366 => 147,  361 => 146,  344 => 145,  336 => 142,  332 => 141,  327 => 138,  325 => 137,  322 => 135,  316 => 131,  303 => 126,  298 => 124,  294 => 123,  290 => 121,  286 => 119,  278 => 117,  276 => 116,  270 => 114,  266 => 113,  260 => 110,  256 => 109,  251 => 106,  249 => 105,  246 => 103,  240 => 99,  228 => 95,  219 => 91,  214 => 89,  208 => 86,  204 => 84,  200 => 82,  192 => 80,  190 => 79,  186 => 77,  182 => 76,  174 => 73,  170 => 72,  165 => 69,  163 => 68,  151 => 58,  147 => 57,  133 => 46,  129 => 45,  122 => 41,  118 => 40,  111 => 36,  107 => 35,  103 => 33,  94 => 26,  89 => 24,  83 => 21,  80 => 20,  74 => 16,  68 => 13,  64 => 12,  59 => 10,  53 => 6,  48 => 3,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/lms_classic/templates/lms-homepage.html.twig", "/var/www/html/web/themes/custom/lms_classic/templates/lms-homepage.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 68, "for" => 76, "set" => 162];
        static $filters = ["escape" => 2, "t" => 10, "default" => 35, "raw" => 36, "length" => 68, "slice" => 124, "striptags" => 124];
        static $functions = ["attach_library" => 2, "path" => 21, "range" => 193];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'for', 'set'],
                ['escape', 't', 'default', 'raw', 'length', 'slice', 'striptags'],
                ['attach_library', 'path', 'range'],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
