<?php

namespace App\Http\Controllers;
use App\Models\PrivateMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

class PrivateMessagesController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/messages/{user}",
 *     summary="Lấy tất cả tin nhắn giữa hai người dùng",
 *     tags={"Messages"},
 *     @OA\Parameter(
 *         name="user",
 *         in="path",
 *         description="Người dùng cần lấy tin nhắn với",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Danh sách tin nhắn giữa hai người dùng",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *             @OA\Property(property="id", type="integer" , description="id tin nhắn"),
 *             @OA\Property(property="sender_id", type="integer" , description="id người gửi tin nhắn"),
 *             @OA\Property(property="receiver_id", type="integer" , description="id người nhận tin nhắn"), 
 *             @OA\Property(property="status", type="integer" , description="trạng thái tin nhắn "),
 *             @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật"),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Lỗi khi không thể tải tin nhắn",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="không thể tải tin nhắn")
 *         )
 *     )
 * )
 */

    public function ShowAllMessage(User $user)
    {
        $user1Id  = Auth::id(); 
        $user2Id = $user->id;
        if($user1Id == $user2Id){
            return response()->json(['error'=> 'không thể tải tin nhắn'],400);
        }
        $messages = PrivateMessage::where(function($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user1Id)
                  ->where('receiver_id', $user2Id);
        })->orWhere(function($query) use ($user1Id, $user2Id) {
            $query->where('sender_id', $user2Id)
                  ->where('receiver_id', $user1Id);
        })->orderBy('created_at', 'asc')->get();
        return response()->json($messages,200);
    }
    /**
     * @OA\Post(
     *     path="/api/messages/{user}",
     *     summary="Gửi tin nhắn tới một người dùng",
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="Người dùng nhận tin nhắn",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Nội dung tin nhắn",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="content", type="string", example="Nội dung tin nhắn")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Tin nhắn đã được gửi thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tin nhắn đã được gửi"),
     *             @OA\Property(property="data", type="object",
     *                @OA\Property(property="id", type="integer" , description="id tin nhắn"),
     *                @OA\Property(property="sender_id", type="integer" , description="id người gửi tin nhắn"),
     *                @OA\Property(property="receiver_id", type="integer" , description="id người nhận tin nhắn"), 
     *                @OA\Property(property="status", type="integer" , description="trạng thái tin nhắn "),
     *                @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
     *                @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Lỗi khi không thể gửi tin nhắn",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="không thể gửi tin nhắn cho chính mình")
     *         )
     *     )
     * )
     */
    public function SendMessages(Request $request , User $user)
    {   
        DB::beginTransaction();
        try{
            $senderID  = Auth::id();
            $receiverId =$user->id;
            if($senderID == $receiverId){
                return response()->json(['error'=> 'không thể gửi tin nhắn cho chính mình'],400);
            }
            $content = $request->input('content');
            $message = new PrivateMessage([
                'sender_id' => $senderID,
                'receiver_id' =>$receiverId ,
                'content' => $content,
                'status' => config('default.private_messages.status.send'),
            ]);
            $message->save();
            DB::commit();
            return response()->json(['message' => 'Tin nhắn đã được gửi','data' => $message], 200); 
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['message'=> $e->getMessage()],400);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/messages/{privateMessage}/{user}",
     *     summary="Cập nhật nội dung tin nhắn",
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="privateMessage",
     *         in="path",
     *         description="Tin nhắn cần cập nhật",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="Người dùng có tin nhắn được cập nhật",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin cập nhật tin nhắn",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="content", type="string", example="Nội dung tin nhắn mới")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Tin nhắn đã được cập nhật thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tin nhắn đã được cập nhật"),
     *             @OA\Property(property="data", type="object",
     *                @OA\Property(property="id", type="integer" , description="id tin nhắn"),
     *                @OA\Property(property="sender_id", type="integer" , description="id người gửi tin nhắn"),
     *                @OA\Property(property="receiver_id", type="integer" , description="id người nhận tin nhắn"), 
     *                @OA\Property(property="status", type="integer" , description="trạng thái tin nhắn "),
     *                @OA\Property(property="created_at", type="string", format="date-time", description="Ngày tạo"),
     *                @OA\Property(property="updated_at", type="string", format="date-time", description="Ngày cập nhật"),
     *             )
     *         )
     *     )
     * )
     */
    public function UpdateMessage(Request $request, PrivateMessage $privateMessage , User $user)
    {
        $privateMessage->update([
            'content' => $request->input('content')
        ]);
        return response()->json(['message' => 'Tin nhắn đã được cập nhật',$privateMessage], 200);
    }
    /**
     * @OA\Delete(
     *     path="/api/message/{privateMessage}/{user}",
     *     summary="Xóa tin nhắn",
     *     tags={"Messages"},
     *     @OA\Parameter(
     *         name="privateMessage",
     *         in="path",
     *         description="Tin nhắn cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="Người dùng có tin nhắn được cập nhật",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Tin nhắn đã được xóa thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tin nhắn đã được xóa")
     *         )
     *     )
     * )
     * )
     */
    public function DeleteMessage( PrivateMessage $privateMessage , User $user)
    {
        $privateMessage->delete();
        return response()->json(['message' => 'Tin nhắn đã được xóa'], 200);
    }
}