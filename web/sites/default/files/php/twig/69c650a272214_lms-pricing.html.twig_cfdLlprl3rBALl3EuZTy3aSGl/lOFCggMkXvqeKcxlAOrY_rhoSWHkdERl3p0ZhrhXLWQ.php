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

/* modules/custom/lms_course/templates/lms-pricing.html.twig */
class __TwigTemplate_433f409b9768480f28a3ebafdb469675 extends Template
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

<section class=\"lms-section\">
  <div class=\"container\">
    <div class=\"lms-pricing-header\">
      <h1>";
        // line 7
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Choose Your Plan"));
        yield "</h1>
      <p>";
        // line 8
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Get unlimited access to courses and certifications"));
        yield "</p>
    </div>

    <div class=\"lms-pricing-grid\">
      ";
        // line 12
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["plans"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["plan"]) {
            // line 13
            yield "        <div class=\"lms-card lms-pricing-card ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "recommended", [], "any", false, false, true, 13)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("lms-pricing-card--recommended") : ("")));
            yield "\">
          ";
            // line 14
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "recommended", [], "any", false, false, true, 14)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 15
                yield "            <div class=\"lms-pricing-card__badge\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Most Popular"));
                yield "</div>
          ";
            }
            // line 17
            yield "          <h3 class=\"lms-pricing-card__name\">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "name", [], "any", false, false, true, 17), "html", null, true);
            yield "</h3>
          <div class=\"lms-pricing-card__price\">
            ";
            // line 19
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "price", [], "any", false, false, true, 19) > 0)) {
                // line 20
                yield "              <span class=\"lms-pricing-card__amount\">\$";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Twig\Extension\CoreExtension']->formatNumber(CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "price", [], "any", false, false, true, 20), 2), "html", null, true);
                yield "</span>
              ";
                // line 21
                $context["period"] = (((CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "billing_cycle", [], "any", false, false, true, 21) == "yearly")) ? ("year") : ((((CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "billing_cycle", [], "any", false, false, true, 21) == "monthly")) ? ("month") : (CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "billing_cycle", [], "any", false, false, true, 21)))));
                // line 22
                yield "              ";
                if ((($context["period"] ?? null) != "lifetime")) {
                    // line 23
                    yield "                <span class=\"lms-pricing-card__period\">/";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t(($context["period"] ?? null)));
                    yield "</span>
              ";
                } else {
                    // line 25
                    yield "                <span class=\"lms-pricing-card__period\"> ";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("lifetime access"));
                    yield "</span>
              ";
                }
                // line 27
                yield "            ";
            } else {
                // line 28
                yield "              <span class=\"lms-pricing-card__amount\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Free"));
                yield "</span>
            ";
            }
            // line 30
            yield "          </div>

          <ul class=\"lms-pricing-card__features\">
            ";
            // line 33
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::split($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "features", [], "any", false, false, true, 33), "
"));
            foreach ($context['_seq'] as $context["_key"] => $context["feature"]) {
                // line 34
                yield "              ";
                if ((($tmp = Twig\Extension\CoreExtension::trim($context["feature"])) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 35
                    yield "                <li><i class=\"fas fa-check\"></i> ";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::trim($context["feature"]), "html", null, true);
                    yield "</li>
              ";
                }
                // line 37
                yield "            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['feature'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 38
            yield "          </ul>

          <a href=\"";
            // line 40
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getUrl("lms_order.checkout_membership", ["plan_id" => CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "id", [], "any", false, false, true, 40)]), "html", null, true);
            yield "\" class=\"lms-btn ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "recommended", [], "any", false, false, true, 40)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("lms-btn--primary") : ("lms-btn--outline")));
            yield " lms-btn--block\">
            ";
            // line 41
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((CoreExtension::getAttribute($this->env, $this->source, $context["plan"], "price", [], "any", false, false, true, 41) > 0)) ? (t("Get Started")) : (t("Start Free"))));
            yield "
          </a>
        </div>
      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['plan'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 45
        yield "    </div>
  </div>
</section>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["plans"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/lms_course/templates/lms-pricing.html.twig";
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
        return array (  164 => 45,  154 => 41,  148 => 40,  144 => 38,  138 => 37,  132 => 35,  129 => 34,  124 => 33,  119 => 30,  113 => 28,  110 => 27,  104 => 25,  98 => 23,  95 => 22,  93 => 21,  88 => 20,  86 => 19,  80 => 17,  74 => 15,  72 => 14,  67 => 13,  63 => 12,  56 => 8,  52 => 7,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/lms_course/templates/lms-pricing.html.twig", "/var/www/html/web/modules/custom/lms_course/templates/lms-pricing.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 12, "if" => 14, "set" => 21];
        static $filters = ["escape" => 2, "t" => 7, "number_format" => 20, "split" => 33, "trim" => 34];
        static $functions = ["attach_library" => 2, "url" => 40];

        try {
            $this->sandbox->checkSecurity(
                ['for', 'if', 'set'],
                ['escape', 't', 'number_format', 'split', 'trim'],
                ['attach_library', 'url'],
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
