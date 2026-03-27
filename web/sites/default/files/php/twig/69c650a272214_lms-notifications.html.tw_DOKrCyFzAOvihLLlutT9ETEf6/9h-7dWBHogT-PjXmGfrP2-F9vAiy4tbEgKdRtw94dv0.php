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

/* modules/custom/lms_notification/templates/lms-notifications.html.twig */
class __TwigTemplate_c7ad6c6e00e70d330da30ace71fef880 extends Template
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
";
        // line 3
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("lms_notification/list"), "html", null, true);
        yield "

<section class=\"lms-section\">
  <div class=\"container\">
    <div class=\"lms-notifications-wrapper\">
      <div class=\"lms-notifications-header\">
        <h1 class=\"lms-notifications-title\">
          <i class=\"fas fa-bell\" style=\"margin-right: 0.5rem; color: #60a5fa;\"></i>";
        // line 10
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Notifications"));
        yield "
        </h1>
        <a href=\"";
        // line 12
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->getPath("lms_notification.preferences"));
        yield "\" class=\"lms-btn-settings use-ajax\" data-dialog-type=\"modal\" data-dialog-options='{\"width\": \"800\", \"title\": \"Notification Preferences\", \"dialogClass\": \"lms-theme-dark-form\"}'>
          <i class=\"fas fa-sliders-h\"></i> ";
        // line 13
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Settings"));
        yield "
        </a>
      </div>

      <div class=\"lms-notification-filters\">
        <a href=\"?type=all\" class=\"lms-filter-btn ";
        // line 18
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_type"] ?? null) == "all")) ? ("active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("All"));
        yield "</a>
        <a href=\"?type=announcement\" class=\"lms-filter-btn ";
        // line 19
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_type"] ?? null) == "announcement")) ? ("active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Announcements"));
        yield "</a>
        <a href=\"?type=forum_reply\" class=\"lms-filter-btn ";
        // line 20
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_type"] ?? null) == "forum_reply")) ? ("active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Community Replies"));
        yield "</a>
        <a href=\"?type=enrollment\" class=\"lms-filter-btn ";
        // line 21
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_type"] ?? null) == "enrollment")) ? ("active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Enrollments"));
        yield "</a>
        <a href=\"?type=quiz_result\" class=\"lms-filter-btn ";
        // line 22
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_type"] ?? null) == "quiz_result")) ? ("active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Quiz Results"));
        yield "</a>
        <a href=\"?type=system\" class=\"lms-filter-btn ";
        // line 23
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_type"] ?? null) == "system")) ? ("active") : ("")));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("System"));
        yield "</a>
      </div>

      ";
        // line 26
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["notifications"] ?? null)) > 0)) {
            // line 27
            yield "        <div class=\"lms-notifications\">
          ";
            // line 28
            $context["type_icons"] = ["enrollment" => "fa-user-plus", "quiz_result" => "fa-clipboard-check", "certificate" => "fa-certificate", "payment" => "fa-credit-card", "forum_reply" => "fa-comment", "system" => "fa-bell", "announcement" => "fa-bullhorn", "reminder" => "fa-clock"];
            // line 38
            yield "          ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["notifications"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["notif"]) {
                // line 39
                yield "            <div class=\"lms-notification-item ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "read", [], "any", false, false, true, 39)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("lms-notification-item--unread") : ("")));
                yield "\">
              <div class=\"lms-notification-item__icon\">
                <i class=\"fas ";
                // line 41
                yield (((CoreExtension::getAttribute($this->env, $this->source, ($context["type_icons"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 41), [], "array", true, true, true, 41) &&  !(null === (($_v0 = ($context["type_icons"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess && in_array($_v0::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v0[CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 41)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["type_icons"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 41), [], "array", false, false, true, 41))))) ? ($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, (($_v1 = ($context["type_icons"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess && in_array($_v1::class, CoreExtension::ARRAY_LIKE_CLASSES, true) ? ($_v1[CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 41)] ?? null) : CoreExtension::getAttribute($this->env, $this->source, ($context["type_icons"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "type", [], "any", false, false, true, 41), [], "array", false, false, true, 41)), "html", null, true)) : ("fa-bell"));
                yield "\"></i>
              </div>
              <div class=\"lms-notification-item__body\">
                <h4>
                  ";
                // line 45
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "link", [], "any", false, false, true, 45)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 46
                    yield "                    <a href=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "link", [], "any", false, false, true, 46), "html", null, true);
                    yield "\">";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "title", [], "any", false, false, true, 46), "html", null, true);
                    yield "</a>
                  ";
                } else {
                    // line 48
                    yield "                    ";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "title", [], "any", false, false, true, 48), "html", null, true);
                    yield "
                  ";
                }
                // line 50
                yield "                </h4>
                ";
                // line 51
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "message", [], "any", false, false, true, 51)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 52
                    yield "                  <p>";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, Twig\Extension\CoreExtension::striptags(CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "message", [], "any", false, false, true, 52)), "html", null, true);
                    yield "</p>
                ";
                }
                // line 54
                yield "                <div class=\"lms-notification-item__meta\">
                  <span class=\"lms-notification-item__date\">
                    <i class=\"far fa-clock\"></i> ";
                // line 56
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Twig\Extension\CoreExtension']->formatDate(CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "created", [], "any", false, false, true, 56), "M d, Y - H:i"), "html", null, true);
                yield "
                  </span>
                  ";
                // line 58
                if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, $context["notif"], "read", [], "any", false, false, true, 58)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 59
                    yield "                    <span class=\"lms-notification-item__new-badge\">";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("New"));
                    yield "</span>
                  ";
                }
                // line 61
                yield "                </div>
              </div>
            </div>
          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['notif'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 65
            yield "        </div>
        
        ";
            // line 67
            if ((($tmp = ($context["pager"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 68
                yield "          ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["pager"] ?? null), "html", null, true);
                yield "
        ";
            }
            // line 70
            yield "      ";
        } else {
            // line 71
            yield "        <div class=\"lms-empty-state\">
          <i class=\"fas fa-bell-slash\"></i>
          <h3>";
            // line 73
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("You have no notifications"));
            yield "</h3>
          <p>";
            // line 74
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($context["current_type"] ?? null) == "all")) ? (t("When you receive notifications, they will appear here.")) : (t("No notifications of this type found."))));
            yield "</p>
        </div>
      ";
        }
        // line 77
        yield "    </div>
  </div>
</section>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["current_type", "notifications", "pager"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/lms_notification/templates/lms-notifications.html.twig";
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
        return array (  220 => 77,  214 => 74,  210 => 73,  206 => 71,  203 => 70,  197 => 68,  195 => 67,  191 => 65,  182 => 61,  176 => 59,  174 => 58,  169 => 56,  165 => 54,  159 => 52,  157 => 51,  154 => 50,  148 => 48,  140 => 46,  138 => 45,  131 => 41,  125 => 39,  120 => 38,  118 => 28,  115 => 27,  113 => 26,  105 => 23,  99 => 22,  93 => 21,  87 => 20,  81 => 19,  75 => 18,  67 => 13,  63 => 12,  58 => 10,  48 => 3,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/lms_notification/templates/lms-notifications.html.twig", "/var/www/html/web/modules/custom/lms_notification/templates/lms-notifications.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 26, "set" => 28, "for" => 38];
        static $filters = ["escape" => 2, "t" => 10, "length" => 26, "striptags" => 52, "date" => 56];
        static $functions = ["attach_library" => 2, "path" => 12];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'set', 'for'],
                ['escape', 't', 'length', 'striptags', 'date'],
                ['attach_library', 'path'],
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
