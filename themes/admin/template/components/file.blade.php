<el-form-item prop="{{ $truename }}" size="small" class="{{ \Arr::get($parameters, 'helptext')?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $truename }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="node.{{ $truename }}"
    native-size="100"
    maxlength="200"
    show-word-limit></el-input>
  @if (\Arr::get($parameters, 'helptext'))
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $parameters['helptext'] }}</span>
  @endif
  <button type="button" class="md-button md-raised md-small md-primary md-theme-default" @click="showMedias('{{ $truename }}')">
    <div class="md-ripple">
      <div class="md-button-content">浏 览</div>
    </div>
  </button>
</el-form-item>
