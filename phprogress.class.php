<?php

date_default_timezone_set('America/Sao_Paulo');

class Porcentagem{
	
	//Configurações
	var $pauseIn = 1; //Modo alto desempenho (0 desligado / 1 ligado)
	var $caracteres = 110; //Tamanho da tela width (integer)
	var $mostrar = array('barra'=>true,'tempo'=>true,'roda'=>true,'fps'=>true); //Define o que ira aparecer na tela (seguir padrao)
	var $idioma = 'pt-Br'; //Idioma padrão da classe
	
	//Variaveis de sistema
	var $inicio = 0;
	var $atual = 0;
	var $ultimaPorcentagem = 0;
	var $runs = 1;
	var $runsPause = 0;
	var $roda = 0;
	var $segAnterior = 0;
	var $telas = 1;
	var $textos = array('pt-Br'=>' restante(s)|Calculando tempo restante...|Concluido em | segundo(s).| Modo alto desempenho.|Calculando FPS...|seg|min|hra|dia(s)',
						'en-Us'=>' remaining|Calculating time remaining...|Completed in | second.| High Performance Mode.|Calculating FPS...|sec|min|hour(s)|day(s)');
	
	public function gerarBarra($porcentagem){
		if($porcentagem > 100) $porcentagem = 100;
		if($porcentagem < 0) $porcentagem = 0;
		$foi = ($this->caracteres - 2) * ($porcentagem / 100);
		$foi = round($foi, 0, PHP_ROUND_HALF_UP);
		system("clear");
		echo '[';
		for($i = 1;$i<=$foi;$i++){
			echo '#';
		}
		for($i = 1;$i<=($this->caracteres-2)-$foi;$i++){
			echo '-';
		}
		echo ']';
	}
	
	public function mostrar($opcoes){
		$this->mostrar = $opcoes;
	}
	
	public function turbo($opcao){
		if($opcao === true){
			$this->pauseIn = 1;
		} else {
			$this->pauseIn = 0;
		}
	}
	
	public function exibir($porcentagem){
		$time = date("U");
		$tempo = $this->mostrar['tempo'];
		$barra = $this->mostrar['barra'];
		$roda = $this->mostrar['roda'];
		$fps = $this->mostrar['fps'];
		
		//$porcentagem = round($porcentagem, 0);
		$this->run($time, $porcentagem);
		$pauseIn = $this->pauseIn;
		$runsPause = $this->runsPause;
		if($this->pauseIn > 0 and $porcentagem != 100){
			if($pauseIn == $runsPause){
				$runsPause = 0;
				$executar = 1;
			} else {
				$runsPause++;
			}
		} else {
			$executar = 1;
		}
		if($executar == 1){
			$this->telas = $this->telas + 1;
			$tempoEstimado = $this->tempoEstimado();
			if($barra === true){
				$this->gerarBarra($porcentagem);
			}
			if(($tempo === true or $roda === true) and $barra === false){
				system("clear");
			}
			if($roda === true and $porcentagem < 99){
				$this->roda();
			}
			if($tempo === true){
				echo $tempoEstimado;
			}
			if($fps === true){
				$this->showFPS();
			}
		}
		$this->runsPause = $runsPause;
	}
	
	public function tempoEstimado(){
		$runs = $this->runs;
		$ultimaPorcentagem = $this->ultimaPorcentagem;
		$atual = $this->atual;
		$inicio = $this->inicio;
		$falta = 0;
		$return = '';
		
		if($runs > 4 and $ultimaPorcentagem > 0){
			$media = ($atual - $inicio) / $ultimaPorcentagem;
			$falta = (100 - $ultimaPorcentagem) * $media;
			if($this->pauseIn < 5000000 and $this->pauseIn > 0){
				if($falta > 1000) $this->pauseIn = $this->pauseIn + 1000;
				if($falta > 100) $this->pauseIn = $this->pauseIn + 100;
				if($falta > 50) $this->pauseIn = $this->pauseIn + 500;
				if($falta > 20) $this->pauseIn = $this->pauseIn + 200;
				if($falta > 10) $this->pauseIn = $this->pauseIn + 10;
			}
			$return .= round($ultimaPorcentagem, 0, PHP_ROUND_HALF_UP).'% | ';
			if($ultimaPorcentagem < 99){
				if($falta > 0){
					$return .= $this->conversorSegudos(number_format($falta, 0, '.', '')).$this->textos[0];
				} else {
					$return .= $this->textos[1];
				}
			} else {
				$return .= $this->textos[2].($atual - $inicio).$this->textos[3]."\n";
			}
		} else {
			$return .= $this->textos[1];
		}
		if($this->pauseIn > 1000 and $ultimaPorcentagem < 99){
			$return .= $this->textos[4];
		}
		return $return;
	}
	
	public function roda(){
		$caracteres = str_split('|/-\\');
		$atual = $this->atual;
		$segAnterior = $this->segAnterior;
		if($segAnterior != $atual){
			if($this->roda >= (count($caracteres) - 1) or $this->roda < 0){
				$this->roda = 0;
			} else {
				$this->roda = $this->roda + 1;
			}
			$this->segAnterior = $atual;
		}
		echo $caracteres[$this->roda].' ';
	} 
	
	private function run($time, $porcentagem){
		$this->atual = $time;
		$this->ultimaPorcentagem = $porcentagem;
		$this->runs = $this->runs + 1;
	}
	
	public function inicio($idioma = null){
		$this->inicio = date("U");
		if(!empty($idioma)){
			$this->idioma = $idioma;
		}
		$textos = explode('|', $this->textos[$this->idioma]);
		$this->textos = $textos;
	}
	
	public function showFPS(){
		$telas = $this->telas;
		$atual = $this->atual;
		$inicio = $this->inicio;
		if(($atual - $inicio) == 0){
			echo "\n".$this->textos[5]."\n";
		} else {
			echo "\n".number_format($telas / ($atual - $inicio), 1, '.', '').' FPS'."\n";
		}
	}
	
	private function conversorSegudos($seg){
		if($seg > 60){ //Minuto
			$return = ($seg / 60);
			$type = $this->textos[7];
		} else if($seg > (60 * 60)){ //Minuto
			$return = ($seg / (60 * 60));
			$type = $this->textos[8];
		} else {
			$return = $seg;
			$type = $this->textos[6];
		}
		return number_format($return, 0, '.', '').$type;
	}
	
	public function concluido(){
		$this->exibir(100);
	}
	
}

?>
