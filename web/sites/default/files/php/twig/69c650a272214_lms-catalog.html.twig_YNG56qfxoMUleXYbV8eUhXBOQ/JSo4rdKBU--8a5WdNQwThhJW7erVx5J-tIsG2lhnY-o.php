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

/* modules/custom/lms_course/templates/lms-catalog.html.twig */
class __TwigTemplate_19fb31750ecf5ed7f6f1ac0b0e380b19 extends Template
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
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("lms_theme/global"), "html", null, true);
        yield "

    <div class=\"lms-catalog\">
      ";
        // line 6
        yield "      <aside class=\"lms-catalog__sidebar\">
        <h3 class=\"lms-catalog__sidebar-title\">";
        // line 7
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("SEARCH FILTERS"));
        yield "</h3>

        ";
        // line 10
        yield "        <div class=\"lms-catalog__filter-group\">
          <input type=\"text\" class=\"form-control lms-input\" placeholder=\"";
        // line 11
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Title"));
        yield "\" id=\"course-search\" value=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "search", [], "any", true, true, true, 11)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "search", [], "any", false, false, true, 11), "")) : ("")), "html", null, true);
        yield "\">
        </div>

        ";
        // line 15
        yield "        <div class=\"lms-catalog__filter-group\">
          <h4>";
        // line 16
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("CATEGORY"));
        yield "</h4>
          <select class=\"form-control lms-select\" id=\"category-filter\">
            <option value=\"\">";
        // line 18
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("All Categories"));
        yield "</option>
            ";
        // line 19
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["categories"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["cat"]) {
            // line 20
            yield "              <option value=\"";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "id", [], "any", false, false, true, 20), "html", null, true);
            yield "\" ";
            if (CoreExtension::inFilter(CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "id", [], "any", false, false, true, 20), CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "category", [], "any", false, false, true, 20))) {
                yield "selected";
            }
            yield ">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "label", [], "any", false, false, true, 20), "html", null, true);
            yield "</option>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['cat'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 22
        yield "          </select>
          ";
        // line 23
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::slice($this->env->getCharset(), ($context["categories"] ?? null), 0, 6));
        foreach ($context['_seq'] as $context["_key"] => $context["cat"]) {
            // line 24
            yield "            <label class=\"lms-catalog__checkbox\">
              <input type=\"checkbox\" value=\"";
            // line 25
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "id", [], "any", false, false, true, 25), "html", null, true);
            yield "\" data-filter-type=\"category\" ";
            if (CoreExtension::inFilter(CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "id", [], "any", false, false, true, 25), CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "category", [], "any", false, false, true, 25))) {
                yield "checked";
            }
            yield ">
              <span>";
            // line 26
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["cat"], "label", [], "any", false, false, true, 26), "html", null, true);
            yield "</span>
            </label>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['cat'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 29
        yield "        </div>

        ";
        // line 32
        yield "        <div class=\"lms-catalog__filter-group\">
          <h4>";
        // line 33
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("LEVEL"));
        yield "</h4>
          ";
        // line 34
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(["Beginner", "Intermediate", "Advanced"]);
        foreach ($context['_seq'] as $context["_key"] => $context["level"]) {
            // line 35
            yield "            <label class=\"lms-catalog__checkbox\">
              <input type=\"checkbox\" value=\"";
            // line 36
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::lower($this->env->getCharset(), $context["level"]), "html", null, true);
            yield "\" data-filter-type=\"level\" ";
            if (CoreExtension::inFilter(Twig\Extension\CoreExtension::lower($this->env->getCharset(), $context["level"]), CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "level", [], "any", false, false, true, 36))) {
                yield "checked";
            }
            yield ">
              <span>";
            // line 37
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t($context["level"]));
            yield "</span>
            </label>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['level'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 40
        yield "        </div>

        ";
        // line 43
        yield "        <div class=\"lms-catalog__filter-group\">
          <h4>";
        // line 44
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("PRICE"));
        yield "</h4>
          <label class=\"lms-catalog__checkbox\">
            <input type=\"checkbox\" value=\"free\" data-filter-type=\"price\" ";
        // line 46
        if (CoreExtension::inFilter("free", CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "price", [], "any", false, false, true, 46))) {
            yield "checked";
        }
        yield ">
            <span>";
        // line 47
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Free"));
        yield "</span>
          </label>
          <label class=\"lms-catalog__checkbox\">
            <input type=\"checkbox\" value=\"paid\" data-filter-type=\"price\" ";
        // line 50
        if (CoreExtension::inFilter("paid", CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "price", [], "any", false, false, true, 50))) {
            yield "checked";
        }
        yield ">
            <span>";
        // line 51
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Paid"));
        yield "</span>
          </label>
        </div>

        ";
        // line 56
        yield "        <div class=\"lms-catalog__filter-group\">
          <h4>";
        // line 57
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("RATING"));
        yield "</h4>
          ";
        // line 58
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable([5, 4, 3, 2, 1]);
        foreach ($context['_seq'] as $context["_key"] => $context["r"]) {
            // line 59
            yield "            <label class=\"lms-catalog__checkbox\">
              <input type=\"checkbox\" value=\"";
            // line 60
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["r"], "html", null, true);
            yield "\" data-filter-type=\"rating\" ";
            if (CoreExtension::inFilter($context["r"], CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "rating", [], "any", false, false, true, 60))) {
                yield "checked";
            }
            yield ">
              <span class=\"lms-rating-stars\">
                ";
            // line 62
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(range(1, 5));
            foreach ($context['_seq'] as $context["_key"] => $context["i"]) {
                // line 63
                yield "                  <i class=\"fas fa-star ";
                if (($context["i"] <= $context["r"])) {
                    yield "text-warning";
                } else {
                    yield "text-muted";
                }
                yield "\"></i>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['i'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 65
            yield "                <span style=\"margin-left: 4px;\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $context["r"], "html", null, true);
            yield " ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Stars"));
            yield "</span>
              </span>
            </label>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['r'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 69
        yield "        </div>
      </aside>

      ";
        // line 73
        yield "      <div class=\"lms-catalog__main\">
        ";
        // line 75
        yield "        <div class=\"lms-catalog__toolbar\" style=\"display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;\">
          <span class=\"lms-catalog__count\">";
        // line 76
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["courses"] ?? null)), "html", null, true);
        yield " ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("courses found"));
        yield "</span>
          <div class=\"lms-catalog__sort\" style=\"display: flex; align-items: center; gap: 0.5rem; white-space: nowrap;\">
            <label style=\"margin: 0;\">";
        // line 78
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Sort by:"));
        yield "</label>
            <select class=\"lms-select lms-select--sm\" id=\"sort-courses\" style=\"width: auto;\">
              <option ";
        // line 80
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "sort", [], "any", false, false, true, 80) == "Most Popular")) {
            yield "selected";
        }
        yield ">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Most Popular"));
        yield "</option>
              <option ";
        // line 81
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "sort", [], "any", false, false, true, 81) == "Newest")) {
            yield "selected";
        }
        yield ">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Newest"));
        yield "</option>
              <option ";
        // line 82
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["filters"] ?? null), "sort", [], "any", false, false, true, 82) == "Price Low-High")) {
            yield "selected";
        }
        yield ">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Price Low-High"));
        yield "</option>
            </select>
          </div>
        </div>

        ";
        // line 88
        yield "        ";
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["courses"] ?? null)) > 0)) {
            // line 89
            yield "          <div class=\"grid-courses\">
            ";
            // line 90
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["courses"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["card"]) {
                // line 91
                yield "              ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ["#theme" => "lms_course_card", "#card" => $context["card"]], "html", null, true);
                yield "
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['card'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 93
            yield "          </div>

          ";
            // line 96
            yield "          ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["pager"] ?? null), "html", null, true);
            yield "
        ";
        } else {
            // line 98
            yield "          <div class=\"lms-empty-state\">
            <i class=\"fas fa-search\"></i>
            <h3>";
            // line 100
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("No courses found"));
            yield "</h3>
            <p>";
            // line 101
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Try adjusting your filters"));
            yield "</p>
          </div>
        ";
        }
        // line 104
        yield "      </div>
    </div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["filters", "categories", "courses", "pager"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/lms_course/templates/lms-catalog.html.twig";
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
        return array (  346 => 104,  340 => 101,  336 => 100,  332 => 98,  326 => 96,  322 => 93,  313 => 91,  309 => 90,  306 => 89,  303 => 88,  291 => 82,  283 => 81,  275 => 80,  270 => 78,  263 => 76,  260 => 75,  257 => 73,  252 => 69,  239 => 65,  226 => 63,  222 => 62,  213 => 60,  210 => 59,  206 => 58,  202 => 57,  199 => 56,  192 => 51,  186 => 50,  180 => 47,  174 => 46,  169 => 44,  166 => 43,  162 => 40,  153 => 37,  145 => 36,  142 => 35,  138 => 34,  134 => 33,  131 => 32,  127 => 29,  118 => 26,  110 => 25,  107 => 24,  103 => 23,  100 => 22,  85 => 20,  81 => 19,  77 => 18,  72 => 16,  69 => 15,  61 => 11,  58 => 10,  53 => 7,  50 => 6,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/lms_course/templates/lms-catalog.html.twig", "/var/www/html/web/modules/custom/lms_course/templates/lms-catalog.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 19, "if" => 20];
        static $filters = ["escape" => 2, "t" => 7, "default" => 11, "slice" => 23, "lower" => 36, "length" => 76];
        static $functions = ["attach_library" => 2, "range" => 62];

        try {
            $this->sandbox->checkSecurity(
                ['for', 'if'],
                ['escape', 't', 'default', 'slice', 'lower', 'length'],
                ['attach_library', 'range'],
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
