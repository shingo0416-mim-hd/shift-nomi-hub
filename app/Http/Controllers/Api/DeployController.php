<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use RuntimeException;

/**
 * デプロイコントローラー
 *
 * GitHubのWebhooksを利用して、指定されたディレクトリに対してデプロイ操作を実行します。
 */
class DeployController extends BaseController
{
    /**
     * デプロイ操作を実行
     *
     * GitHubからのWebhookリクエストを受け取り、指定されたディレクトリに対してデプロイ操作を実行します。
     *
     * @param Request $request HTTPリクエスト
     * @return \Illuminate\Http\JsonResponse レスポンスメッセージを含むJSONレスポンス
     */
    public function deploy(Request $request)
    {
        // GitHubからのX-Hub-Signatureヘッダーを取得
        $signature = $request->header('X-Hub-Signature');

        // ペイロードの内容を取得
        $payload = $request->getContent();

        // GitHubで設定したSecret
        $secret = config('deploy.webhook_secret');

        // HMACハッシュを生成
        $hash = 'sha1=' . hash_hmac('sha1', $payload, $secret);

        // デプロイがアクティブでない場合は処理を終了
        if (!config('deploy.is_active')) {
            Log::channel('deploy')->info('Deploy is not active. skipped the deploy.');

            return response()->json(['message' => 'Deploy is not active']);
        }

        // GitHubからのハッシュと生成したハッシュを比較
        if (hash_equals($hash, $signature)) {
            try {
                // 実行対象ディレクトリとブランチ名を設定ファイルから取得
                $repositoryDirectory = escapeshellarg(config('deploy.repository_directory'));
                $backendDirectory = escapeshellarg(config('deploy.backend_directory'));
                $frontendDirectory = escapeshellarg(config('deploy.frontend_directory'));
                $nvmDir = escapeshellarg(config('deploy.nvm_dir'));
                $nodeVersion = escapeshellarg(config('deploy.node_version'));
                $branch = escapeshellarg(config('deploy.branch'));

                // 現在のリモートリポジトリの最新履歴を取得
                $this->runCommand("sudo -u ec2-user /usr/bin/git -C $repositoryDirectory fetch -p");

                // 現在の変更を一時的に保存
                $this->runCommand("sudo -u ec2-user /usr/bin/git -C $repositoryDirectory stash");

                // stashで保存された変更を破棄
                $this->runCommand("sudo -u ec2-user /usr/bin/git -C $repositoryDirectory stash clear");

                // 指定したブランチにチェックアウト
                $this->runCommand("sudo -u ec2-user /usr/bin/git -C $repositoryDirectory checkout $branch");

                // 最新の変更を取得
                $this->runCommand("sudo -u ec2-user /usr/bin/git -C $repositoryDirectory pull");

                // Composerを使って依存関係をインストールまたはアップデート
                $this->runCommand("sudo -u ec2-user /usr/local/bin/composer -d $backendDirectory install");

                // フロントエンドの依存関係をインストール
                $this->runCommand(
                    "sudo -u ec2-user bash -lc 'export NVM_DIR=\"\$HOME/.nvm\"; " .
                    "[ -s \"\$NVM_DIR/nvm.sh\" ] && . \"\$NVM_DIR/nvm.sh\"; " .
                    "nvm use $nodeVersion >/dev/null; " .
                    "cd $frontendDirectory && npm ci --include=dev'"
                );

                // フロントエンドをビルド
                $this->runCommand(
                    "sudo -u ec2-user bash -lc 'export NVM_DIR=\"\$HOME/.nvm\"; " .
                    "[ -s \"\$NVM_DIR/nvm.sh\" ] && . \"\$NVM_DIR/nvm.sh\"; " .
                    "nvm use $nodeVersion >/dev/null; " .
                    "cd $frontendDirectory && npm run build'"
                );

                // supervisordの再起動
                $this->runCommand("sudo -u ec2-user supervisorctl restart all");

                Log::channel('deploy')->info('Deploy successful. Checked out branch name is ' . $branch);

                return response()->json(['message' => 'Deploy successful. Checked out branch name is ' . $branch]);
            } catch (RuntimeException $e) {
                Log::channel('deploy')->error('Deploy failed.', [
                    'branch' => config('deploy.branch'),
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'message' => 'Deploy failed',
                    'detail' => $e->getMessage(),
                ]);
            }
        } else {
            // 認証失敗
            Log::channel('deploy')->info('Webhook signature does not match.');

            return response()->json(['error' => 'Invalid signature'], 403);
        }
    }

    /**
     * 外部コマンドを実行
     *
     * 指定されたコマンドを実行し、エラーが発生した場合はログにエラーメッセージを記録します。
     *
     * @param string $command 実行するコマンド
     * @return array コマンドの実行結果を含む配列
     */
    private function runCommand($command)
    {
        $output = [];
        $return_var = 0;
        exec($command . ' 2>&1', $output, $return_var);
        if ($return_var !== 0) {
            Log::channel('deploy')->error('Command failed.', compact('command', 'output', 'return_var'));
            throw new RuntimeException('Command failed: ' . $command);
        }
        return $output;
    }
}
